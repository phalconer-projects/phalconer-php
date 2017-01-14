<?php

namespace phalconer\provider;

use LogicException;
use Phalcon\DiInterface;
use Phalcon\Mvc\User\Component;

abstract class AbstractServiceProvider extends Component
{
    /**
     * @var string
     */
    protected $serviceName;
    
    /**
     * @var array
     */
    protected $config;
    
    final public function __construct($serviceName, array $config, DiInterface $di)
    {
        if ($serviceName) {
            $this->serviceName = $serviceName;
        } else if (!$this->serviceName) {
            throw new LogicException(
                sprintf('The service defined in "%s" cannot have an empty name.', get_class($this))
            );
        }
        
        if (empty($config)) {
            $configService = config('phalcon.services', false);
            if ($configService !== false && isset($configService[$this->serviceName])) {
                $config = $configService[$this->serviceName];
            }
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
