<?php

namespace phalconer\provider;

use phalconer\provider\AbstractServiceProvider;

class SecurityServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    protected $serviceName = 'security';
    
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
                    $class = $config->get('class', \Phalcon\Security::class);
                    $security = new $class();
                    $workFactor = $config->get('workFactor', false);
                    if ($workFactor) {
                        $security->setWorkFactor($workFactor);
                    }
                    
                    return $security;
                }
            );
        }
    }
}