<?php

namespace phalconer\provider;

use phalconer\provider\AbstractServiceProvider;
use Phalcon\Flash\Direct;

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
                    return new Direct($config['direct']);
                }
            );
        }
    }
}