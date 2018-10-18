<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/26
 * Time: 9:35
 */

namespace LyPenguin\Support;

use LyPenguin\Core\Exceptions\InvalidArgumentException;

class Attribute extends Collection
{
    /**
     * Attributes alias.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Auto snake attribute name.
     *
     * @var bool
     */
    protected $snakeable = true;

    /**
     * Required attributes.
     *
     * @var array
     */
    protected $requirements = [];

    /**
     * Constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Set attribute.
     *
     * @param string $attribute
     * @param string $value
     *
     * @return Attribute
     */
    public function setAttribute($attribute, $value)
    {
        $this->set($attribute, $value);

        return $this;
    }

    public function getAttribute($attribute, $default)
    {
        return $this->get($attribute, $default);
    }

    /**
     * Attribute validation.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate($attribute, $value)
    {
        return true;
    }

    /**
     * @param $attribute
     * @param $value
     * @return $this
     * @throws InvalidArgumentException
     */
    public function with($attribute, $value)
    {
        $this->snakeable && $attribute = Str::snake($attribute);
        if (!$this->validate($attribute, $value)) {
            throw new InvalidArgumentException("Invalid attribute '{$attribute}'.");
        }
        $this->set($attribute, $value);

        return $this;
    }

    /**
     * Return the raw name of attribute.
     * 返回属性的原始名称
     * @param string $key
     *
     * @return string
     */
    protected function getRealKey($key)
    {
        //通过值去找key
        if ($alias = array_search($key, $this->aliases, true)) {
            $key = $alias;
        }

        return $key;
    }

    /**
     * Override parent set() method.
     *
     * @param string $attribute
     * @param mixed $value
     */
    public function set($attribute, $value = null)
    {
        parent::set($this->getRealKey($attribute), $value);
    }

    /**
     * Override parent get() method.
     *
     * @param string $attribute
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($attribute, $default = null)
    {
        return parent::get($this->getRealKey($attribute), $default);
    }

    /**
     * @param $method
     * @param $args
     * @return Attribute
     * @throws InvalidArgumentException
     */
    public function __call($method, $args)
    {
        if (stripos($method, 'with') === 0) {
            $method = substr($method, 4);
        }

        return $this->with($method, array_shift($args));
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return Attribute
     * @throws InvalidArgumentException
     */
    public function __set($property, $value)
    {
        return $this->with($property, $value);
    }

    /**
     * Whether or not an data exists by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return parent::__isset($this->getRealKey($key));
    }

    /**
     * Check required attributes.
     *
     * @throws InvalidArgumentException
     */
    protected function checkRequiredAttributes()
    {
        foreach ($this->requirements as $attribute) {
            if (!isset($this->$attribute)) {
                throw new InvalidArgumentException(" '{$attribute}' cannot be empty.");
            }
        }
    }

    /**
     * Return all items.
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function all()
    {
        $this->checkRequiredAttributes();

        return parent::all();
    }
}