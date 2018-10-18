<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/25
 * Time: 9:04
 */

namespace LyPenguin\OpenPlatform\Api;

use Symfony\Component\HttpFoundation\RedirectResponse;

class PreAuthorization extends AbstractOpenPlatform
{
    /**
     * Pre auth link.
     */
    const PRE_AUTH_LINK = 'https://auth.om.qq.com/omoauth2/authorize?response_type=code&client_id=%s&redirect_uri=%s&state=%s';

    /**
     * 引导用户去授权
     * @param $url
     * @param array $state
     * @return RedirectResponse
     */
    public function redirect($url, $state = [])
    {
        if (!is_array($state)) {
            $state = [$state];
        }
        $state = json_encode($state);
        $state = urlencode($state);

        return new RedirectResponse(sprintf(self::PRE_AUTH_LINK, $this->getAppId(), urlencode($url), $state));
    }
}