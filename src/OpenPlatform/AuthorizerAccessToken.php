<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/26
 * Time: 8:54
 */

namespace LyPenguin\OpenPlatform;

use LyPenguin\Core\AccessToken as BaseAccessToken;

class AuthorizerAccessToken extends BaseAccessToken {
    /**
     * @var Authorizer
     */
    protected $authorizer;

    public function __construct($clientId, Authorizer $authorizer) {
        parent::__construct($clientId, null);

        $this->authorizer = $authorizer;
    }

    /**
     * 运行时调用api发现token过期通过refresh_token去刷新
     * @return mixed
     * @throws \LyPenguin\Core\Exception
     * @throws \LyPenguin\Core\Exceptions\HttpException
     */
    protected function renewAccessToken() {
        $Api = $this->authorizer->getApi();
        //刷新某个企鹅号的token
        $token = $Api->getAuthorizerToken($this->authorizer->getAppId(), $this->authorizer->getRefreshToken());

        $this->authorizer->setAccessToken($token['data']['access_token'], $token['data']['expires_in'] - 1500);
        return $token['data']['access_token'];
    }

    /**
     * @param bool $forceRefresh
     * @return mixed
     * @throws \LyPenguin\Core\Exception
     * @throws \LyPenguin\Core\Exceptions\HttpException
     */
    public function getToken($forceRefresh = false) {
        $cached = $this->authorizer->getAccessToken();

        if ($forceRefresh || empty($cached)) {
            return $this->renewAccessToken();
        }

        return $cached;
    }

    /**
     * @return string
     * @throws \LyPenguin\Core\Exception
     */
    public function getAppId() {
        return $this->authorizer->getAppId();
    }
}