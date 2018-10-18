<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/26
 * Time: 9:35
 */

namespace LyPenguin\Message;

use LyPenguin\Support\Attribute;

abstract class AbstractMessage extends Attribute
{
    /**
     * Message type.
     *
     * @var string
     */
    protected $type;

    /**
     * Message id.
     *
     * @var int
     */
    protected $id;

    /**
     * 发送消息到哪个用户去
     * Message target user open id.
     *
     * @var string
     */
    protected $to;

    /**
     * 消息是从哪个用户来的
     * Message sender open id.
     *
     * @var string
     */
    protected $from;

    /**
     * Message attributes.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Return type name message.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Magic getter.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return parent::__get($property);
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return $this|Attribute
     * @throws \LyPenguin\Core\Exceptions\InvalidArgumentException
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            parent::__set($property, $value);
        }

        return $this;
    }
}