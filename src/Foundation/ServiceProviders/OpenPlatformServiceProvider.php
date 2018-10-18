<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/25
 * Time: 8:42
 */

namespace LyPenguin\Foundation\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use LyPenguin\OpenPlatform\Api\PreAuthorization;
use LyPenguin\OpenPlatform\AccessToken;
use LyPenguin\OpenPlatform\OpenPlatform;
use LyPenguin\OpenPlatform\Api\BaseApi;
use LyPenguin\OpenPlatform\Authorizer;
use LyPenguin\Foundation\Application;
use LyPenguin\OpenPlatform\AuthorizerAccessToken;

class OpenPlatformServiceProvider implements ServiceProviderInterface {

    public function register(Container $pimple) {
        //通过三方的token组件，其实只用到了clinet_id和client_secret，并没有真正的发出请求
        $pimple['open_platform.access_token'] = function ($pimple) {
            return new AccessToken(
                $pimple['config']['open_platform']['client_id'],
                $pimple['config']['open_platform']['client_secret'],
                $pimple['cache']
            );
        };
        //引导用户去授权
        $pimple['open_platform.pre_auth'] = function ($pimple) {
            return new PreAuthorization(
                $pimple['open_platform.access_token'],
                $pimple['request']
            );
        };

        //成为三方
        $pimple['open_platform'] = function ($pimple) {
            return new OpenPlatform($pimple);
        };

        //通过授权得到的token能调用的api组件
        $pimple['open_platform.api'] = function ($pimple) {
            return new BaseApi(
                $pimple['open_platform.access_token'],
                $pimple['request']
            );
        };

        //用于从缓存中获取和设置授权成功的企鹅号的token、refresh_token组件
        //理解为refresh_token从数据库中取出来，然后存到这个类里面，这个类会将它存入缓存，和从缓存中获取它，运行时的一个token容器
        $pimple['open_platform.authorizer'] = function ($pimple) {
            return new Authorizer(
                $pimple['open_platform.api'],
                $pimple['config']['open_platform']['client_id'],
                $pimple['cache']
            );
        };


        $pimple['open_platform.authorizer_access_token'] = function ($pimple) {
            return new AuthorizerAccessToken(
                $pimple['config']['open_platform']['client_id'],
                $pimple['open_platform.authorizer']
            );
        };

        //应用
        $pimple['open_platform.app'] = function ($pimple) {
            return new Application($pimple['config']->toArray());
        };
    }
}