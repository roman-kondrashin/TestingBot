<?php

namespace Service;

use Application\Config;
use Storage\CacheableInterface;
use Storage\CacheableTrait;
use Storage\File\Model\Ini;

class ServiceLocator implements ServiceLocatorInterface, CacheableInterface
{
    use CacheableTrait;

    /** @var ServiceLocator */
    private static $obj;

    private function __construct() {}
    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}

    /**
     * @return ServiceLocator
     */
    public static function getInstance()
    {
        if (null === self::$obj) {
            self::$obj = new self();
        }

        return self::$obj;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        if (null !== ($cache = $this->getCache($name))) {
            return $cache;
        }

        $method = $this->getConfig()[$name];
        $class = $method();

        $this->saveCache($name, $class);

        return $class;
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        return [
            'config' => function () {
                return $this->get(Config::class);
            },
            'configfile' => function () {
                return new Ini('config/config.ini');
            },
            Config::class => function () {
                return new Config($this->get('configfile'));
            },
        ];
    }
}
