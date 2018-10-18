<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/21
 * Time: 11:47
 */

namespace LyPenguin\Core;

use  LyPenguin\Support\Log;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use LyPenguin\Support\Collection;
use LyPenguin\Core\Exceptions\HttpException;

class AbstractAPI
{

    /**
     * http请求组件
     * @var Http
     */
    protected $http;

    /**
     * token组件
     * @var AccessToken
     */
    protected $accessToken;

    const GET = 'get';
    const POST = 'post';
    const JSON = 'json';

    /**
     * @var int
     */
    protected static $maxRetries = 2;

    /**
     * AbstractAPI constructor.
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        $this->setAccessToken($accessToken);
    }

    /**
     * @param AccessToken $accessToken
     * @return $this
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param int $retries
     */
    public static function maxRetries($retries)
    {
        self::$maxRetries = abs($retries);
    }

    /**
     * 调用api接口数据返回，解析三方返回的json数据
     * @param $method
     * @param array $args
     * @return Collection
     * @throws Exceptions\HttpException
     */
    public function parseJSON($method, array $args)
    {
        $http = $this->getHttp();
        $result = call_user_func_array([$http, $method], $args);
        $contents = $http->parseJSON($result);
        $this->checkAndThrow($contents);
        return new Collection($contents);
    }

    /**
     * 这里检查企鹅号的接口是否返回正确的数据格式，只有正确的格式整体的流程才会往下面走
     * 非正确的数据格式，直接抛出异常
     * 函数体的逻辑是照抄的，这个调试的时候，看返回什么在做响应的处理
     * @param array $contents
     * @throws HttpException
     */
    protected function checkAndThrow(array $contents)
    {
//        /**
//         * 逻辑是照抄微信的，需要改成企鹅号的，错误返回格式
//         */
//        if (isset($contents['errcode']) && 0 !== $contents['errcode']) {
//            if (empty($contents['errmsg'])) {
//                $contents['errmsg'] = 'Unknown';
//            }
//
//            throw new HttpException($contents['errmsg'], $contents['errcode']);
//        }
    }

    /**
     * @return Http
     */
    public function getHttp()
    {
        if (is_null($this->http)) {
            $this->http = new Http();
        }
        if (count($this->http->getMiddlewares()) === 0) {
            //注册中间件
            $this->registerHttpMiddlewares();
        }
        return $this->http;
    }

    /**
     * @param Http $http
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;

        return $this;
    }


    /**
     * 注册自定义中间件
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // log,特殊中间件
        $this->http->addMiddleware($this->logMiddleware());
        // retry，特殊中间件
        $this->http->addMiddleware($this->retryMiddleware());

        //发送请求中间件
        // access token
        $this->http->addMiddleware($this->accessTokenMiddleware());
    }

    /**
     * 日志中间件
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        //tap 在发送请求之前和之后调用回调的中间件。
        //第一个参数是发送前的处理函数，传递给第二个参数的匿名函数是得到响应之后的处理的
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::getLogger()->debug("Request: {$request->getMethod()} {$request->getUri()} " . json_encode($options));
            Log::getLogger()->debug('Request headers:' . json_encode($request->getHeaders()));
        });
    }

    /**
     * 重连中间件
     * @return callable
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            if ($retries <= self::$maxRetries && $response && $body = $response->getBody()) {
                if ((stripos($body, '43001') !== false) || (stripos($body, '40015') !== false)) {
                    $field = $this->accessToken->getQueryName();
                    //刷新token
                    $token = $this->accessToken->getToken(true);

                    $request = $request->withUri($newUri = Uri::withQueryValue($request->getUri(), $field, $token));
                    Log::getLogger()->debug("Retry with Request Token: {$token}");
                    Log::getLogger()->debug("Retry with Request Uri: {$newUri}");

                    return true;
                }
            }
            return false;
        });
    }

    /**
     * 将要发送请求时候的中间，该作用是将请求的token放到url?后面
     * Attache access token to request query.
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if (!$this->accessToken) {
                    return $handler($request, $options);
                }

                $field = $this->accessToken->getQueryName();
                $token = $this->accessToken->getToken();

                $request = $request->withUri(Uri::withQueryValue($request->getUri(), $field, $token));
                return $handler($request, $options);
            };
        };
    }

}