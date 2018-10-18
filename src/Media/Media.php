<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/29
 * Time: 14:08
 */

namespace LyPenguin\Media;

use LyPenguin\Core\AbstractAPI;

class Media extends AbstractAPI
{
    const API_MEDIA_INFO = 'https://api.om.qq.com/media/basicinfoauth';

    /**
     * 获取授权企鹅号的信息
     * @param $openid
     * @return \LyPenguin\Support\Collection
     * @throws \LyPenguin\Core\Exceptions\HttpException
     */
    public function getMediaInfo($openid)
    {

        $params = [
            'openid' => $openid
        ];
        return $this->parseJSON('get', [self::API_MEDIA_INFO, $params]);
    }
}