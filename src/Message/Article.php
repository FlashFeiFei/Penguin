<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/26
 * Time: 10:13
 */

namespace LyPenguin\Message;


class Article extends AbstractMessage
{

    /**
     * 这个属性作用好像只是给我们看的，并没有作用
     * @var array
     */
    protected $properties = [
        //文章标题
        'title',
        //文章内容
        'content',
        //文章封面图,多封面图用逗号分隔
        'cover_pic',
        //文章封面类型	,非必填
        'cover_type',
        //文章标签,非必填
        'tag',
        //文章分类编号,非必填
        'category',
        //申请原创文章	,非必填
        'apply',
        //原创首发平台,非必填
        'original_platform',
        //原创首发链接,非必填
        'original_url',
        //原创首发作者,非必填
        'original_author',
    ];

}