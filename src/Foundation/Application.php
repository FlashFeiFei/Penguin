<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/20
 * Time: 13:55
 */

namespace LyPenguin\Foundation;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Cache\Cache as CacheInterface;
use Doctrine\Common\Cache\FilesystemCache;
use LyPenguin\Core\AccessToken;
use LyPenguin\Core\Http;
use LyPenguin\Core\AbstractAPI;

/**
 * Class Application
 *
 * @property \LyPenguin\OpenPlatform\OpenPlatform $open_platform
 * @property \LyPenguin\Material\Material $material
 * @property \LyPenguin\Media\Media $media
 */
class Application extends Container
{

    /**
     * 注册服务提供者
     * @var array
     */
    protected $providers = [
        ServiceProviders\OpenPlatformServiceProvider::class,
        ServiceProviders\MaterialServiceProvider::class,
        ServiceProviders\MediaServiceProvider::class
    ];

    /**
     * Application constructor.
     * @param $config
     */
    public function __construct($config)
    {
        parent::__construct();

        $this['config'] = function () use ($config) {
            return new Config($config);
        };

        if ($this['config']['debug']) {
            error_reporting(E_ALL);
        }
        //注册服务提供者
        $this->registerProviders();
        $this->registerBase();

        Http::setDefaultOptions($this['config']->get('guzzle', ['timeout' => 5.0]));

        AbstractAPI::maxRetries($this['config']->get('max_retries', 2));


    }

    /**
     * Add a provider.
     *
     * @param string $provider
     *
     * @return Application
     */
    public function addProvider($provider)
    {
        array_push($this->providers, $provider);

        return $this;
    }

    /**
     * Set providers.
     *
     * @param array $providers
     */
    public function setProviders(array $providers)
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Register providers.
     */
    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    /**
     * Register basic providers.
     */
    private function registerBase()
    {
        $this['request'] = function () {
            return Request::createFromGlobals();
        };

        if (!empty($this['config']['cache']) && $this['config']['cache'] instanceof CacheInterface) {
            $this['cache'] = $this['config']['cache'];
        } else {
            $this['cache'] = function () {
                return new FilesystemCache(sys_get_temp_dir());
            };
        }

        $this['access_token'] = function () {
            return new AccessToken(
                $this['config']['client_id'],
                $this['config']['client_secret'],
                $this['cache']
            );
        };
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * 企鹅号好像没有提供这个api，待写
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        if (is_callable([$this['fundamental.api'], $method])) {
            return call_user_func_array([$this['fundamental.api'], $method], $args);
        }

        throw new \Exception("Call to undefined method {$method}()");
    }
}