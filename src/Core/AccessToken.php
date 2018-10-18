<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/21
 * Time: 9:02
 */

namespace LyPenguin\Core;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use LyPenguin\Core\Exceptions\HttpException;

class AccessToken
{
    /**
     * App ID.
     *
     * @var string
     */
    protected $client_id;

    /**
     * App secret.
     *
     * @var string
     */
    protected $client_secret;

    /**
     * Cache.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Cache Key.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Http instance.
     *
     * @var Http
     */
    protected $http;

    /**
     * Query name.
     *
     * @var string
     */
    protected $queryName = 'access_token';

    /**
     * Response Json key name.
     *
     * @var string
     */
    protected $tokenJsonKey = 'access_token';

    /**
     * Cache key prefix.
     *
     * @var string
     */
    protected $prefix = 'penguin.common.access_token.';

    // API
    const API_TOKEN_GET = 'https://auth.om.qq.com/omoauth2/accesstoken';

    /**
     * AccessToken constructor.
     * @param $client_id
     * @param $client_secret
     * @param Cache|null $cache
     */
    public function __construct($client_id, $client_secret, Cache $cache = null)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->cache = $cache;
    }

    /**
     * Set access token cache key.
     *
     * @param string $cacheKey
     *
     * @return $this
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * 缓存key
     * Get access token cache key.
     *
     * @return string $this->cacheKey
     */
    public function getCacheKey()
    {
        if (is_null($this->cacheKey)) {
            return $this->prefix . $this->client_id;
        }

        return $this->cacheKey;
    }

    /**
     * Return the http instance.
     *
     * @return Http
     */
    public function getHttp()
    {
        return $this->http ?: $this->http = new Http();
    }

    /**
     * Set the http instance.
     *
     * @param Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;
        return $this;
    }

    /**
     * Set the access token prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Return the cache manager.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }

    /**
     * Set cache instance.
     *
     * @param \Doctrine\Common\Cache\Cache $cache
     *
     * @return AccessToken
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * 设置自定义 token.
     *
     * @param string $token
     * @param int $expires
     *
     * @return $this
     */
    public function setToken($token, $expires = 7200)
    {
        $this->getCache()->save($this->getCacheKey(), $token, $expires - 1500);

        return $this;
    }

    /**
     * Return the app id.
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->client_id;
    }

    /**
     * Return the secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->client_secret;
    }

    /**
     * 请求别的api是需要token时候的key名
     * Set the query name.
     *
     * @param string $queryName
     *
     * @return $this
     */
    public function setQueryName($queryName)
    {
        $this->queryName = $queryName;

        return $this;
    }

    /**
     * Return the query name.
     *
     * @return string
     */
    public function getQueryName()
    {
        return $this->queryName;
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function getQueryFields()
    {
        return [$this->queryName => $this->getToken()];
    }

    /**
     * 获取token
     * @param bool $forceRefresh
     * @return mixed
     * @throws HttpException
     */
    public function getToken($forceRefresh = false)
    {
        $cacheKey = $this->getCacheKey();
        //从缓存中得到token
        $cached = $this->getCache()->fetch($cacheKey);

        //是否需要刷新token
        if ($forceRefresh || empty($cached)) {
            $token = $this->getTokenFromServer();
            //缓存token,减少1500秒的原因是为了，让中间件在token没过期的时候，发送了请求，这时，可以通过中间件，去刷新token，延长token的有效期
            $this->getCache()->save($cacheKey, $token[$this->tokenJsonKey], $token['expires_in'] - 1500);

            return $token[$this->tokenJsonKey];
        }
        return $cached;
    }

    /**
     * 获取token服务
     * @return string
     * @throws HttpException
     */
    public function getTokenFromServer()
    {
        //企鹅号获取token api所需要的参数
        $params = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'clientcredentials',
        ];
        $http = $this->getHttp();
        //企鹅号获取token是post请求
        $token = $http->post(self::API_TOKEN_GET, $params);
        $token = $http->parseJSON($token);
        if ($token['code'] === '0' && isset($token['data']) && !empty($token['data'])) {
            $result = $token['data'];
            if (empty($result[$this->tokenJsonKey])) {
                //如果请求的接口失败，也就是获取token失败，那就抛出异常，因为这个问题是三方那边服务器返回的，除非是我们自己传错参数
                throw new HttpException('Request AccessToken fail. response: ' . json_encode($token, JSON_UNESCAPED_UNICODE));
            }
            return $result;
        }
        throw new HttpException('Request AccessToken fail. response: ' . json_encode($token, JSON_UNESCAPED_UNICODE));
    }
}