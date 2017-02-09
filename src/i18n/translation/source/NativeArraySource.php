<?php

namespace phalconer\i18n\translation\source;

use Phalcon\Translate\Adapter\NativeArray;

class NativeArraySource extends AbstractSource
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
     * {@inheritdoc}
     */
    public function __construct(array $params = NULL)
    {
        parent::__construct($params);
        if ($params !== NULL) {
            $this->setMessages(isset($params['messages']) ? $params['messages'] : []);
            $this->setMessagesDir(isset($params['messagesDir']) ? $params['messagesDir'] : '');
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function makeAdapter($language)
    {
        if (empty($this->messages) && !empty($this->messagesDir)) {
            $translationFile = $this->messagesDir . $language . '.php';
            if (is_file($translationFile)) {
                $this->messages[$language] = require $translationFile;
            }
        }
        return new NativeArray([
            'content' => isset($this->messages[$language]) ? $this->messages[$language] : [],
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function add($language, $label, $translation)
    {
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
     * @return NativeArraySource this
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
     * @return NativeArraySource this
     */
    public function setMessagesDir($messagesDir = NULL)
    {
        $this->messagesDir = realpath($messagesDir) . '/';
        return $this;
    }
}
