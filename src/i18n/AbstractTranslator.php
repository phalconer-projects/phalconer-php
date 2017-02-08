<?php

namespace phalconer\i18n;

use Phalcon\Config;
use Phalcon\DiInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Response;
use Phalcon\Translate\AdapterInterface;

abstract class AbstractTranslator
{
    /**
     * @var DiInterface 
     */
    protected $di;
    
    /**
     * @var AdapterInterface
     */
    protected $translationAdapter;
    
    /**
     * @var string
     */
    protected $defaultLanguage;
    
    /**
     * @var array
     */
    protected $supportedLanguages = [];
    
    /**
     * @param DiInterface $di
     * @param Config $config
     */
    public function __construct(DiInterface $di, Config $config = NULL)
    {
        $this->di = $di;
        if ($config !== NULL) {
            $this->defaultLanguage = $config->get('defaultLanguage', NULL);
            $this->supportedLanguages = $config->get('supportLanguages', []);
        }
    }
    
    /**
     * @param string $event
     */
    public function registerRedirectDispatcherEvent()
    {
        $dispatcher = $this->di->get('dispatcher');
        if (!$dispatcher->getEventsManager()) {
            $dispatcher->setEventsManager(new EventsManager());
        }
        $di = $this->di;
        $dispatcher->getEventsManager()->attach(
            "dispatch:beforeExecuteRoute",
            function (Event $event, $dispatcher) use ($di)
            {
                $language = $dispatcher->getParam('language');
                $currentLanguage = $this->currentLanguage();
                if (!empty($language)) {
                    $this->setupLanguage($language);
                    $currentLanguage = $this->currentLanguage();
                }
                if ($language !== $currentLanguage) {
                    $di->get('response')->redirect('/' . $currentLanguage . $di->get('router')->getRewriteUri());
                }
                return $dispatcher;
            }
        );
    }
    
    /**
     * @param string $language
     * @return bool
     */
    public function canTranslate($language)
    {
        return !empty($language) && in_array($language, $this->supportedLanguages);
    }
    
    /**
     * @param string $language
     */
    public abstract function makeTranslationAdapter($language);
    
    /**
     * @param string $language
     * @param string $label
     * @param string $translation
     */
    public abstract function add($language, $label, $translation);

        /**
     * @return AdapterInterface
     */
    public function getTranslationAdapter()
    {
        if (!isset($this->translationAdapter)) {
            $language = $this->currentLanguage();
            $this->translationAdapter = $this->makeTranslationAdapter($language);
        }
        return $this->translationAdapter;
    }
    
    /**
     * @param AdapterInterface $translationAdapter
     * @return \phalconer\i18n\AbstractTranslator this
     */
    function setTranslationAdapter(AdapterInterface $translationAdapter)
    {
        $this->translationAdapter = $translationAdapter;
        return $this;
    }
       
    /**
     * @param string $wontedLanguage
     * @return string
     */
    public function getCorrectLanguageBy($wontedLanguage)
    {
        return in_array($wontedLanguage, $this->supportedLanguages) ? $wontedLanguage : $this->defaultLanguage;
    }
    
    /**
     * @param string $wontedLanguage
     * @return \phalconer\i18n\AbstractTranslator this
     */
    public function setupLanguage($wontedLanguage = NULL)
    {
        if (empty($wontedLanguage)) {
            $language = $this->getBestLanguage();
        } else {
            $language = $this->getCorrectLanguageBy($wontedLanguage);
        }
        return $this->setLanguage($language);
    }
    
    /**
     * @return string|null
     */
    public function currentLanguage()
    {
        $language = $this->getLanguage();
        if ($language === NULL) {
            return $this->setupLanguage()->getLanguage();
        }
        return $language;
    }
    
    /**
     * @return string|null
     */
    public function getLanguage()
    {
        if ($this->di->get('session')->has('language')) {
            return $this->di->get('session')->get('language');
        } else {
            return NULL;
        }
    }
    
    /**
     * 
     * @param string $language
     * @return \phalconer\i18n\AbstractTranslator this
     */
    public function setLanguage($language)
    {
        $this->di->get('session')->set('language', $language);
        return $this;
    }
    
    /**
     * @return string
     */
    public function getBestLanguage()
    {
        $languages = [];
        $acceptLanguagesLine = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if ($acceptLanguagesLine && !empty($this->supportedLanguages)) {
            $matches = [];
            if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $acceptLanguagesLine, $matches)) {
                $languages = array_combine($matches[1], $matches[2]);
                foreach ($languages as $lang => $priority) {
                    $languages[$lang] = $priority ? $priority : 1;
                }
                arsort($languages, SORT_NUMERIC);
            }
        }
        foreach ($languages as $lang => $priority) {
            $lang = strtok($lang, '-');
            if (in_array($lang, $this->supportedLanguages)) {    
                return $lang;
            }
        }
        return $this->defaultLanguage;
    }
    
    /**
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }
    
    /**
     * @param string $defaultLanguage
     * @return \phalconer\i18n\AbstractTranslator this
     * @throws \Exception
     */
    public function setDefaultLanguage($defaultLanguage)
    {
        $this->checkSupportedLanguage($defaultLanguage);
        $this->defaultLanguage = $defaultLanguage;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getSupportedLanguages()
    {
        return $this->supportedLanguages;
    }
    
    /**
     * @param array $supportedLanguages
     * @return \phalconer\i18n\AbstractTranslator this
     */
    public function setSupportedLanguages($supportedLanguages)
    {
        $this->supportedLanguages = $supportedLanguages;
        return $this;
    }
    
    /**
     * @param string $language
     * @throws \Exception if language is unsupported
     */
    protected function checkSupportedLanguage($language)
    {
        if (!in_array($language, $this->supportedLanguages)) {
            throw new \Exception("Unsupported language: $language");
        }
    }
}
