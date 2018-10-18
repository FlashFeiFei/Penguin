<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/25
 * Time: 16:31
 */

namespace LyPenguin\OpenPlatform;

use Doctrine\Common\Cache\Cache;
use LyPenguin\Core\Exception;
use LyPenguin\OpenPlatform\Api\BaseApi;

/**
 * Class Authorizer
 * 解释一下这个类的作用
 * 用于企鹅号授权后，从缓存中设置和获取授权后的token和refresh_token
 * @package App\Modules\Penguin\Lib\OpenPlatform
 */
class Authorizer
{

    //授权企鹅号的token缓存key
    const CACHE_KEY_ACCESS_TOKEN = 'penguin.open_platform.authorizer_access_token';
    //授权企鹅号refresh_token的key
    const CACHE_KEY_REFRESH_TOKEN = 'penguin.open_platform.authorizer_refresh_token';


    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var BaseApi
     */
    protected $api;
    /**
     * 授权的企鹅号
     * @var string
     */
    protected $openid;

    /**
     * 三方的client_id
     *
     * @var string
     */
    protected $openPlatformClientId;


    public function __construct(BaseApi $api, $openPlatformClientId, Cache $cache)
    {
        $this->api = $api;
        $this->openPlatformClientId = $openPlatformClientId;
        $this->cache = $cache;
    }

    /**
     * Gets the base api.
     *
     * @return BaseApi
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * 设置授权的企鹅号的openid
     *  Sets the authorizer app id.
     * @param $openid
     * @return $this
     */
    public function setAppId($openid)
    {
        $this->openid = $openid;

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getAppId()
    {
        if (!$this->openid) {
            throw new Exception(
                'Authorizer App Id is not present, you may not make the authorizer yet.'
            );
        }

        return $this->openid;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getAccessTokenCacheKey()
    {
        return self::CACHE_KEY_ACCESS_TOKEN . $this->openid . $this->getAppId();
    }

    /**
     * 设置授权方token的缓存
     * @param $token
     * @param int $expires
     * @return $this
     * @throws Exception
     */
    public function setAccessToken($token, $expires = 7200)
    {
        $this->cache->save($this->getAccessTokenCacheKey(), $token, $expires);

        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getAccessToken()
    {
        return $this->cache->fetch($this->getAccessTokenCacheKey());
    }

    /**
     * 获取refresh_token的key
     * @return string
     * @throws Exception
     */
    public function getRefreshTokenCacheKey()
    {
        return self::CACHE_KEY_REFRESH_TOKEN . $this->openid . $this->getAppId();
    }

    /**
     * 缓存refresh_token
     * @param $refreshToken
     * @return $this
     * @throws Exception
     */
    public function setRefreshToken($refreshToken)
    {
        $this->cache->save($this->getRefreshTokenCacheKey(), $refreshToken);

        return $this;
    }

    /**
     * 获得refresh_token
     * @return mixed
     * @throws Exception
     */
    public function getRefreshToken()
    {
        if ($token = $this->cache->fetch($this->getRefreshTokenCacheKey())) {
            return $token;
        }

        throw new Exception(
            'Authorizer Refresh Token is not present, you may not make the authorizer yet.'
        );
    }

}