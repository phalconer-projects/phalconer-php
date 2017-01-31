<?php

namespace phalconer;

/**
 * @const APPLICATION_ENV Current application stage:
 *        production, staging, development, testing
 */
defined('APPLICATION_ENV') or define('APPLICATION_ENV', 'development');

require_once __DIR__ . '/helpers.php';

use Phalcon\Config;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Error\Handler as ErrorHandler;
use Phalcon\Loader;
use Phalcon\Mvc\Application as MvcApplication;
use phalconer\provider\ServiceProviderFactory;
use phalconer\i18n\AbstractTranslator;

class Application
{
    /**
     * The internal application core.
     * @var \Phalcon\Application
     */
    private $app;

    /**
     * @var DiInterface
     */
    private $di;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        /* Init helper config */
        config($config);
        
        /* Init Phalcon loader */
        $configLoader = config('loader', false);
        if ($configLoader !== false) {
            $loader = new Loader();
            if (isset($configLoader['namespaces'])) {
                $loader->registerNamespaces($configLoader['namespaces']->toArray())->register();
            }
        }
        
        /* Init Phalcon DI */
        $this->di = new FactoryDefault;
        Di::setDefault($this->di);
        $this->di->setShared('web-app', $this);
        $this->di->setShared('config', config());
                
        /** @noinspection PhpIncludeInspection */
        $services = config('services', false);
        if (is_config($services)) {
            $this->initializeServices($services);
        }
        
        $error = config('error', false);
        if ($error) {
            ErrorHandler::register();
        }
        
        $this->app = new MvcApplication($this->di);
        $this->di->setShared('mvc-app', $this->app);
        $this->app->setDI($this->di);
    }
    
    /**
     * Initialize the Services.
     *
     * @param  string[] $services
     * @return $this
     */
    protected function initializeServices(Config $services)
    {
        foreach ($services as $name => $configValue) {
            if ($configValue instanceof \Closure) {
                $this->di->setShared($name, $configValue);
            } else {
                if (is_string($configValue)) {
                    $name = $configValue;
                    $configValue = new Config([]);
                }
                
                if (isset($configValue['provider'])) {
                    $provider = new $configValue['provider']($name, $configValue, $this->di);
                } else {
                    $provider = ServiceProviderFactory::make($name, $configValue, $this->di);
                }
                
                $provider->register();
                $provider->boot();
            }
        }
        return $this;
    }
    
    /**
     * Runs the Application
     *
     * @return \Phalcon\Application|string
     */
    public function run()
    {
        return $this->getHandleContent();
    }
    
    /**
     * Handle application content.
     *
     * @return string
     */
    public function getHandleContent()
    {
        if ($this->app instanceof MvcApplication) {
            return $this->app->handle()->getContent();
        }
        return $this->app->handle();
    }
    
    /**
     * Get the Application.
     *
     * @return \Phalcon\Application|\Phalcon\Mvc\Micro
     */
    public function getApplication()
    {
        return $this->app;
    }
    
    /**
     * @return AbstractTranslator 
     */
    public function getTranslator()
    {
        if ($this->di->has('translator')) {
            $translator = $this->di->getShared('translator');
        } else {
            $class = config('i18n.class', false);
            if ($class) {
                $translator = new $class(config('i18n'));
            } else {
                $translator = new i18n\NativeArrayTranslator();
            }
            $this->di->setShared('translator', $translator);
        }
        return $translator;
    }
    
    /**
     * @param AbstractTranslator $translator
     * @return \phalconer\Application this
     */
    public function setTranslator($translator)
    {
        $this->di->setShared('translator', $translator);
        return $this;
    }
}
