<?php

namespace phalconer\i18n;

use Phalcon\Config;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Adapter\NativeArray;

class NativeArrayTranslator/* implements AdapterInterface*/
{
    /**
     * @var string
     */
    protected $messagesDir;
    
    /**
     * @var string
     */
    protected $defaultLanguage;
    
    /**
     * @var array
     */
    protected $supportLanguages;
    
    /**
     * @param Config $config
     */
    public function __construct($config)
    {
        $this->messagesDir = realpath($config['messages'] . '/');
        $this->defaultLanguage = $config['defaultLanguage'];
        $this->supportLanguages = $config->get('languages', []);
    }

    public function canTranslate($language)
    {
        return !empty($language) && in_array($language, $this->supportLanguages);
    }
    
    public function getTranslation($language)
    {
        if (in_array($language, $this->supportLanguages)) {
            $translationFile = $this->messagesDir . $language . ".php";
        } else {
            $translationFile = $this->messagesDir . $this->defaultLanguage . ".php";
        }
        
        return new NativeArray([
            "content" => require $translationFile,
        ]);
    }
}
