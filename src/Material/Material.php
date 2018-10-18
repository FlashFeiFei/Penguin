<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/26
 * Time: 9:27
 */

namespace LyPenguin\Material;

use LyPenguin\Core\AbstractAPI;
use LyPenguin\Core\AccessToken;
use LyPenguin\Message\Article;

class Material extends AbstractAPI
{

    const API_ARTICLE_AUTH_PUBPIC = 'https://api.om.qq.com/article/authpubpic';
    const API_ARTICLE_CLIENT_PUBPIC = 'https://api.om.qq.com/article/clientpubpic';

    public function __construct(AccessToken $accessToken)
    {
        parent::__construct($accessToken);
    }

    /**
     * 发布文章
     * 授权用户调用
     * @param $articles
     * @return \LyPenguin\Support\Collection
     * @throws \LyPenguin\Core\Exceptions\HttpException
     */
    public function authPubpicArticle($articles)
    {
        if (!empty($articles['title']) || $articles instanceof Article) {
            $articles = [$articles];
        }
        $params = array_map(function ($article) {
            if ($article instanceof Article) {
                //只取需要的
                return $article->only([
                    'title', 'content', 'cover_pic'
                ]);
            }
            return $article;
        }, $articles);

        //企鹅号暂时不支持多图文发布，所以只取第一个元素
        $params = array_shift($params);
        //企鹅号需要多一个openid这个参数
        $params['openid'] = $this->getAccessToken()->getAppId();
        return $this->parseJSON('post', [self::API_ARTICLE_AUTH_PUBPIC, $params]);

    }

    /**
     * 发布文章
     * 自己的企鹅号调用的，非授权的
     * @param $articles
     * @return \LyPenguin\Support\Collection
     * @throws \LyPenguin\Core\Exceptions\HttpException
     */
    public function clientPubpicArticle($articles)
    {
        if (!empty($articles['title']) || $articles instanceof Article) {
            $articles = [$articles];
        }
        $params = array_map(function ($article) {
            if ($article instanceof Article) {
                //只取需要的
                return $article->only([
                    'title', 'content', 'cover_pic'
                ]);
            }
            return $article;
        }, $articles);

        //企鹅号暂时不支持多图文发布，所以只取第一个元素
        $params = array_shift($params);
        return $this->parseJSON('post', [self::API_ARTICLE_CLIENT_PUBPIC, $params]);
    }
}