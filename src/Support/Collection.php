<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/20
 * Time: 13:58
 */

namespace LyPenguin\Support;

//遍历的数据容器
use ArrayAccess;
//ArrayIterator迭代器会把对象或数组封装为一个可以通过foreach来操作的类
use ArrayIterator;
//继承Countable接口的可被用于count() 函数。
use Countable;
//foreach的时候被调用的返回遍历数据的对象
use IteratorAggregate;
use JsonSerializable;
use Serializable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Serializable
{

    /**
     * The collection data.
     *
     * @var array
     */
    protected $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Return all items.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }


    /**
     * Return specific items.
     * 获取一组值
     * @param array $keys
     *
     * @return array
     */
    public function only(array $keys)
    {
        $return = [];

        foreach ($keys as $key) {
            $value = $this->get($key);

            if (!is_null($value)) {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * 重新实例化一个对象
     * Get all items except for those with the specified keys.
     *
     * @param mixed $keys
     *
     * @return static
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Arr::except($this->items, $keys));
    }

    /**
     * Merge data.
     *
     * @param Collection|array $items
     *
     * @return array
     */
    public function merge($items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }

        return $this->all();
    }

    /**
     * To determine Whether the specified element exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return !is_null(Arr::get($this->items, $key));
    }

    /**
     * Retrieve the first item.
     *
     * @return mixed
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Retrieve the last item.
     *
     * @return bool
     */
    public function last()
    {
        $end = end($this->items);

        reset($this->items);

        return $end;
    }

    /**
     * add the item value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function add($key, $value)
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Remove item form Collection.
     *
     * @param string $key
     */
    public function forget($key)
    {
        Arr::forget($this->items, $key);
    }

    /**
     * Set the item value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Retrieve item from Collection.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Build to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Build to json.
     *
     * @param int $option
     *
     * @return string
     */
    public function toJson($option = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->all(), $option);
    }

    /**
     * To string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Get a data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Assigns a value to the specified data.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
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
        return $this->has($key);
    }

    /**
     * Unsets an data by key.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $this->forget($key);
    }

    /**
     * var_export.
     *
     * @return array
     */
    public function __set_state()
    {
        return $this->all();
    }


    public function getIterator()
    {
        // TODO: Implement getIterator() method.
        return new ArrayIterator($this->items);

    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->offsetExists($offset) ? $this->get($offset) : null;
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        if ($this->offsetExists($offset)) {
            $this->forget($offset);
        }
    }

    public function serialize()
    {
        // TODO: Implement serialize() method.
        return serialize($this->items);
    }

    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
        return $this->items = unserialize($serialized);
    }

    public function count()
    {
        // TODO: Implement count() method.
        return count($this->items);
    }

    /**
     * Specify data which should be serialized to JSON.
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return $this->items;
    }


}