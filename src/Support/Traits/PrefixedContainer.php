<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/25
 * Time: 9:27
 */

namespace LyPenguin\Support\Traits;

use Pimple\Container;
use  LyPenguin\Support\Str;

trait PrefixedContainer
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * PrefixedContainer constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Fetches from pimple container.
     *
     * @param string $key
     * @param callable|null $callable
     *
     * @return mixed
     */
    public function fetch($key, callable $callable = null)
    {
        $instance = $this->$key;

        if (!is_null($callable)) {
            $callable($instance);
        }

        return $instance;

    }

    public function __get($key)
    {
        $className = basename(str_replace('\\', '/', static::class));

        $name = Str::snake($className) . '.' . $key;

        return $this->container->offsetGet($name);
    }
}