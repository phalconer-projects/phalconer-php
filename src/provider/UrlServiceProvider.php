<?php

namespace phalconer\provider;

use phalconer\provider\AbstractServiceProvider;
use Phalcon\Mvc\Url;

class UrlServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    protected $serviceName = 'url';
    
    /**
     * {@inheritdoc}
     * The URL component is used to generate all kind of urls in the application.
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
                    $url = new Url();
                    $url->setStaticBaseUri($config->get('staticBaseUri', '/'));
                    $url->setBaseUri($config->get('baseUri', '/'));
                    return $url;
                }
            );
        }
    }
}