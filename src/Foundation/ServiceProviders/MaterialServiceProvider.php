<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/26
 * Time: 9:25
 */

namespace LyPenguin\Foundation\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use LyPenguin\Material\Material;

class MaterialServiceProvider implements ServiceProviderInterface {

    public function register(Container $pimple) {
        $pimple['material'] = function ($pimple) {
            return new Material($pimple['access_token']);
        };
    }
}