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
        if (is_array($this->config)) {
            $config = $this->config;
            
            $this->di->setShared(
                $this->serviceName,
                function() use($config) {
                    $url = new Url();
                    if (!empty($config['staticBaseUri'])) {
                        $url->setStaticBaseUri($config['staticBaseUri']);
                    } else {
                        $url->setStaticBaseUri('/');
                    }
                    if (!empty($config['baseUri'])) {
                        $url->setBaseUri($config['baseUri']);
                    } else {
                        $url->setBaseUri('/');
                    }
                    return $url;
                }
            );
        }
    }
}