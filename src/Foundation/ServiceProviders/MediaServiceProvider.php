<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/29
 * Time: 14:06
 */

namespace LyPenguin\Foundation\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use LyPenguin\Media\Media;

class MediaServiceProvider implements ServiceProviderInterface {

    public function register(Container $pimple) {
        $pimple['media'] = function ($pimple) {
            return new Media($pimple['access_token']);
        };
    }

}