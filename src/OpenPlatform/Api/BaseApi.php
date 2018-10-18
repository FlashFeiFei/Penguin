<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/25
 * Time: 14:35
 */

namespace LyPenguin\OpenPlatform\Api;


class BaseApi extends AbstractOpenPlatform
{

    /**
     * Get auth info api.
     * 通过code获取换取第三方授权access_token
     */
    const GET_AUTH_INFO = 'https://auth.om.qq.com/omoauth2/accesstoken';

    /**
     * Get authorizer token api.
     * 通过refresh_token刷新token
     */
    const GET_AUTHORIZER_TOKEN = 'https://auth.om.qq.com/omoauth2/refreshtoken';


    /**
     * 重写注册自定义中间件，因为企鹅号的授权流程与微信的不同，企鹅号的授权流程不需要或许自己的token
     * 所以将token中间件取消掉就好
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // log,特殊中间件
        $this->http->addMiddleware($this->logMiddleware());
        // retry，特殊中间件
        $this->http->addMiddleware($this->retryMiddleware());

//        //发送请求中间件
//        // access token
//        $this->http->addMiddleware($this->accessTokenMiddleware());
    }


    /**
     * 通过code换取第三方授权access_token
     * @param null $authCode
     * @return \LyPenguin\Support\Collection
     * @throws \LyPenguin\Core\Exceptions\HttpException
     */
    public function getAuthorizationInfo($authCode = null)
    {
        $params = [
            'client_id' => $this->getAppId(),
            'client_secret' => $this->getSecret(),
            'grant_type' => 'authorization_code',
            'code' => $authCode ?: $this->request->get('code'),
        ];
        return $this->parseJSON('post', [self::GET_AUTH_INFO, $params]);
    }

    /**
     * 通过refresh_token刷新token
     * 授权的企鹅号标识
     * @param $openid
     * @param $refreshToken
     * @return \LyPenguin\Support\Collection
     * @throws \LyPenguin\Core\Exceptions\HttpException
     */
    public function getAuthorizerToken($openid, $refreshToken)
    {
        $params = [
            'client_id' => $this->getAppId(),
            'grant_type' => 'refreshtoken',
            'openid' => $openid,
            'refresh_token' => $refreshToken
        ];
        return $this->parseJSON('post', [self::GET_AUTHORIZER_TOKEN, $params]);
    }

}