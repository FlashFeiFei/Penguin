<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/25
 * Time: 9:25
 */

namespace LyPenguin\OpenPlatform;

use LyPenguin\Support\Traits\PrefixedContainer;
use LyPenguin\Foundation\Application;

/**
 * Class OpenPlatform
 * @property \LyPenguin\OpenPlatform\Api\BaseApi $api
 * @property \LyPenguin\OpenPlatform\Api\PreAuthorization $pre_auth
 *
 * @method \LyPenguin\Support\Collection getAuthorizationInfo($authCode = null)
 */
class OpenPlatform
{
    use PrefixedContainer;

    /**
     * 通过refreshToken去创建一个授权的企鹅号应用
     * @param $openid
     * @param $refreshToken
     * @return Application
     */
    public function createAuthorizerApplication($openid, $refreshToken)
    {

        //实例化一个授权成功的企鹅号
        $this->fetch('authorizer', function ($authorizer) use ($openid, $refreshToken) {
            $authorizer->setAppId($openid);
            $authorizer->setRefreshToken($refreshToken);
        });

        return $this->fetch('app', function ($app) {
            //将应用所需要的access_token替换成授权用户的token
            $app['access_token'] = $this->fetch('authorizer_access_token');
        });
    }

    /**
     * Quick access to the base-api.
     * 通过授权的得到token调用一些api
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->api, $method], $args);
    }
}