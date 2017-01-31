<?php

namespace phalconer\i18n;

use Phalcon\Config;
use Phalcon\Translate\AdapterInterface;

abstract class AbstractTranslator
{
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
     * @param Config $config
     */
    public function __construct(Config $config = NULL)
    {
        if ($config !== NULL) {
            $this->defaultLanguage = $config->get('defaultLanguage', NULL);
            $this->supportedLanguages = $config->get('supportLanguages', []);
        }
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
    public abstract function makeTranslationAdapter($language = NULL);
    
    /**
     * @param string $language
     * @return AdapterInterface
     */
    public function getTranslationAdapter($language = NULL)
    {
        if (!isset($this->translationAdapter)) {
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
    public function getLanguage($wontedLanguage)
    {
        return in_array($wontedLanguage, $this->supportedLanguages) ? $wontedLanguage : $this->defaultLanguage;
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
        if (!in_array($defaultLanguage, $this->supportedLanguages)) {
            throw new \Exception("Unsupported language: $defaultLanguage");
        }
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
}
