<?php

namespace phalconer\provider;

use LogicException;
use Phalcon\DiInterface;
use Phalcon\Config;

class ServiceProviderFactory
{
    public static $defaultProviders = [
        'url' => UrlServiceProvider::class,
        'view' => ViewServiceProvider::class,
        'session' => SessionServiceProvider::class,
        'router' => RouterServiceProvider::class,
        'db' => DatabaseServiceProvider::class,
        'modelsMetadata' => ModelsMetadataServiceProvider::class,
        'flash' => FlashServiceProvider::class,
    ];
    
    public static function make($serviceName, Config $config, DiInterface $di)
    {
        $providerClass = self::$defaultProviders[$serviceName];
        if (!empty($providerClass)) {
            return new $providerClass($serviceName, $config, $di);
        } else {
            throw new LogicException(
                sprintf('The service provider class for name "%s" cannot found.', $serviceName)
            );
        }
    }
}
