<?php

namespace phalconer\provider;

use LogicException;
use Phalcon\DiInterface;
use Phalcon\Config;
use Phalcon\Mvc\User\Component;

abstract class AbstractServiceProvider extends Component
{
    /**
     * @var string
     */
    protected $serviceName;
    
    /**
     * @var Config
     */
    protected $config;
    
    final public function __construct($serviceName, Config $config, DiInterface $di)
    {
        if ($serviceName) {
            $this->serviceName = $serviceName;
        } else if (!$this->serviceName) {
            throw new LogicException(
                sprintf('The service defined in "%s" cannot have an empty name.', get_class($this))
            );
        }
        
        if (empty($config)) {
            $config = config('services.' . $this->serviceName, $config);
        }
        $this->config = $config;
        
        $this->setDI($di);
        $this->configure();
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->serviceName;
    }
    
    /**
     * @return void
     */
    public function boot()
    {
    }
    
    /**
     * @return void
     */
    public function configure()
    {
    }
}
