<?php

namespace phalconer\i18n\translation;

use Phalcon\Config;
use Phalcon\DiInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Translate\AdapterInterface;
use phalconer\i18n\translation\source\AbstractSource;
use phalconer\i18n\translation\source\NativeArraySource;

class Translator extends AbstractSource
{
    /**
     * @var DiInterface 
     */
    protected $di;
    
    /**
     * @var string
     */
    protected $defaultLanguage;
    
    /**
     * @var array
     */
    protected $supportedLanguages = [];
    
    /**
     * @var string
     */
    protected $defaultSourceName;
    
    /**
     * @var array
     */
    protected $sources = [];
    
    /**
     * @param DiInterface $di
     * @param Config $config
     */
    public function __construct(DiInterface $di, Config $config = NULL)
    {
        $this->di = $di;
        if ($config !== NULL) {
            $this->defaultLanguage = $config->get('defaultLanguage', NULL);
            $supportedLanguages = $config->get('supportedLanguages', []);
            if (!empty($supportedLanguages)) {
                $this->supportedLanguages = $supportedLanguages->toArray();
            }
            $sources = $config->get('sources', []);
            if (!empty($sources)) {
                $this->initSourcesList($sources->toArray());
            }
            $this->defaultSourceName = $config->get('defaultSourceName', $this->getFirstSourceName());
        }
    }
    
    /**
     * @param array $sources
     */
    public function initSourcesList(array $sources)
    {
        foreach ($sources as $name => $params) {
            $class = isset($params['class']) ? $params['class'] : NativeArraySource::class;
            if (!isset($params['di']) && !empty($this->di)) {
                $params['di'] = $this->di;
            }
            $this->sources[$name] = new $class($params);
        }
    }
    
    /**
     * @return string|null
     */
    protected function getFirstSourceName()
    {
        return empty($this->sources) ? NULL : array_keys($this->sources)[0];
    }

    /**
     * @return AbstractSource|null
     */
    public function getDefaultSource()
    {
        if ($this->defaultSourceName !== NULL) {
            return $this->sources[$this->defaultSourceName];
        }
        return NULL;
    }
    
    /**
     * @param string $name
     * @return AbstractSource|null
     */
    public function getSource($name = NULL)
    {
        if ($name === NULL) {
            return $this->getDefaultSource();
        }
        if (isset($this->sources[$name])) {
            return $this->sources[$name];
        }
        return NULL;
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
     * @param string $sourceName
     * @return AdapterInterface|null
     */
    public function getAdapter($sourceName = NULL)
    {
        $source = $this->getSource($sourceName);
        if (!empty($source)) {
            $language = $this->currentLanguage();
            return $source->makeAdapter($language);
        }
        return NULL;
    }
    
    /**
     * {@inheritdoc}
     */
    public function makeAdapter($language)
    {
        $source = $this->getDefaultSource();
        if (!empty($source)) {
            return $source->makeAdapter($language);
        }
        return NULL;
    }
    
    /**
     * {@inheritdoc}
     */
    public function add($language, $label, $translation)
    {
        $this->checkSupportedLanguage($language);
        $source = $this->getDefaultSource();
        if (!empty($source)) {
           $source->add($language, $label, $translation);
        }
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
     * @return Translator this
     */
    public function setupLanguage($wontedLanguage = NULL)
    {
        if (empty($wontedLanguage)) {
            $language = $this->getBestLanguage();
        } else {
            $language = $this->getCorrectLanguageBy($wontedLanguage);
        }
        $this->setLanguageToSession($language);
        $this->setLanguageToDI($language);
        $this->setLanguageToCoockies($language);
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function currentLanguage()
    {
        $language = $this->getLanguageFromSession();
        if ($language === NULL) {
            $language = $this->getLanguageFromCoockies();
            if ($language === NULL) {
                $language = $this->setupLanguage()->getLanguageFromSession();
            }
        }
        return $language;
    }
    
    /**
     * @return string|null
     */
    public function getLanguageFromSession()
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
     * @return Translator this
     */
    public function setLanguageToSession($language)
    {
        if ($this->di->has('session')) {
            $this->di->get('session')->set('language', $language);
        }
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getLanguageFromCoockies()
    {
        if ($this->di->get('cookies')->has('language')) {
            return $this->di->get('cookies')->get('language')->getValue();
        } else {
            return NULL;
        }
    }
    
    /**
     * 
     * @param string $language
     * @return Translator this
     */
    public function setLanguageToCoockies($language)
    {
        if ($this->di->has('cookies')) {
            $this->di->get('cookies')->set('language', $language, time() + 365 * 86400);
        }
        return $this;
    }
    
    /**
     * 
     * @param string $language
     * @return Translator this
     */
    public function setLanguageToDI($language)
    {
        $this->di->set('language', function() use($language) { return $language; });
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
     * @return Translator this
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
     * @return Translator this
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
    
    /**
     * @return string
     */
    function getDefaultSourceName()
    {
        return $this->defaultSourceName;
    }

    /**
     * @param string $defaultSourceName
     * @return Translator this
     */
    function setDefaultSourceName($defaultSourceName)
    {
        $this->defaultSourceName = $defaultSourceName;
        return $this;
    }
}
