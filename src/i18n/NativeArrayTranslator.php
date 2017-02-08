<?php

namespace phalconer\i18n;

use Phalcon\Config;
use Phalcon\DiInterface;
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
     * {@inheritDoc}
     */
    public function __construct(DiInterface $di, Config $config = NULL)
    {
        parent::__construct($di, $config);
        if ($config !== NULL) {
            $this->setMessages($config->get('messages', []));
            $this->setMessagesDir($config->get('messagesDir', ''));
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function makeTranslationAdapter($language)
    {
        if (empty($this->messages) && !empty($this->messagesDir)) {
            $translationFile = $this->messagesDir . $language . ".php";
            if (is_file($translationFile)) {
                $this->messages[$language] = require $translationFile;
            }
        }
        return new NativeArray([
            "content" => isset($this->messages[$language]) ? $this->messages[$language] : [],
        ]);
    }
    
    /**
     * {@inheritDoc}
     */
    public function add($language, $label, $translation)
    {
        $this->checkSupportedLanguage($language);
        if (!isset($this->messages[$language])) {
            $this->messages[$language] = [];
        }
        $this->messages[$language][$label] = $translation;
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
