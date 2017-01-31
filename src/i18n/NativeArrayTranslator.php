<?php

namespace phalconer\i18n;

use Phalcon\Config;
use Phalcon\Translate\Adapter\NativeArray;

class NativeArrayTranslator extends AbstractTranslator
{
    /**
     * @var array
     */
    protected $messages = [];
    
    /**
     * @var string
     */
    protected $messagesDir;
    
    /**
     * @param Config $config
     */
    public function __construct(Config $config = NULL)
    {
        parent::__construct($config);
        if ($config !== NULL) {
            $this->setMessages($config->get('messages', []));
            $this->setMessagesDir($config->get('messagesDir', ''));
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function makeTranslationAdapter($language = NULL)
    {
        $lang = $this->getLanguage($language);
        if (empty($this->messages) && !empty($this->messagesDir)) {
            $translationFile = $this->messagesDir . $lang . ".php";
            if (is_file($translationFile)) {
                $this->messages[$lang] = require $translationFile;
            }
        }
        return new NativeArray([
            "content" => isset($this->messages[$lang]) ? $this->messages[$lang] : [],
        ]);
    }
    
    /**
     * @return array
     */
    function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     * @return \phalconer\i18n\NativeArrayTranslator this
     */
    function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessagesDir()
    {
        return $this->messagesDir;
    }
    
    /**
     * @param string $messagesDir
     * @return \phalconer\i18n\NativeArrayTranslator this
     */
    public function setMessagesDir($messagesDir = NULL)
    {
        $this->messagesDir = realpath($messagesDir) . '/';
        return $this;
    }
}
