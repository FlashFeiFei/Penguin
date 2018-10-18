<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/25
 * Time: 8:51
 */

namespace LyPenguin\OpenPlatform\Api;

use LyPenguin\Core\AbstractAPI;
use LyPenguin\Core\AccessToken;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractOpenPlatform extends AbstractAPI
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(AccessToken $accessToken, Request $request)
    {
        parent::__construct($accessToken);

        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->getAccessToken()->getAppId();
    }

    public function getSecret()
    {
        return $this->getAccessToken()->getSecret();
    }
}