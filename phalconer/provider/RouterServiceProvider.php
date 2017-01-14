<?php

namespace phalconer\provider;

use phalconer\provider\AbstractServiceProvider;
use Phalcon\Mvc\Router;

class RouterServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    protected $serviceName = 'router';
    
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register()
    {
        if (is_array($this->config)) {
            $config = $this->config;
            
            $this->di->setShared(
                $this->serviceName,
                function() use($config) {
                    $router = new Router();
                    
                    $router->removeExtraSlashes(true);
                    
                    if (!empty($config['routes']) && is_array($config['routes'])) {
                        foreach ($config['routes'] as $pattern => $params) {
                            call_user_func_array([$router, "add"], array_merge([$pattern], $params));
                        }
                    }
                    
                    $router->setDefaultNamespace($config['namespace']);
                    return $router;
                }
            );
        }
    }
}