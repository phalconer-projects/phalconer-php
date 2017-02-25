<?php

namespace phalconer\provider;

use phalconer\provider\AbstractServiceProvider;

class FlashServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    protected $serviceName = 'flash';
    
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register()
    {
        if (is_config($this->config)) {
            $config = $this->config;
            
            $this->di->setShared(
                $this->serviceName,
                function() use($config) {
                    $class = $config->get('class', \Phalcon\Flash\Direct::class);
                    $flash = new $class($config->get('styles', []));
                    return $flash;
                }
            );
        }
    }
}