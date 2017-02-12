<?php

namespace phalconer\provider;

use Phalcon\Crypt;
use phalconer\provider\AbstractServiceProvider;

class CryptServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    protected $serviceName = 'crypt';
    
    /**
     * {@inheritdoc}
     * The URL component is used to generate all kind of urls in the application.
     *
     * @return void
     */
    public function register()
    {
        if (is_config($this->config)) {
            $key = $this->config->get('key', NULL);
            if (empty($key)) {
                throw new \Phalcon\Crypt\Exception("Register service $this->serviceName: key cannot be empty");
            }
            
            $this->di->setShared(
                $this->serviceName,
                function() use($key) {
                    $crypt = new Crypt();
                    $crypt->setKey($key);
                    
                    return $crypt;
                }
            );
        }
    }
}