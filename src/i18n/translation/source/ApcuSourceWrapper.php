<?php

namespace phalconer\i18n\translation\source;

use phalconer\i18n\translation\source\AbstractSource;
use phalconer\i18n\translation\source\NativeArraySource;
use phalconer\i18n\translation\source\adapter\ApcuAdapterWrapper;

class ApcuSourceWrapper extends AbstractSource
{
    /**
     * @var AbstractSource
     */
    protected $source;
    
    /**
     * @var string
     */
    protected $translationsScope;
    
    /**
     * @var int
     */
    protected $ttl;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $params = NULL)
    {
        parent::__construct($params);
        if ($params !== NULL) {
            $class = isset($params['source']['class']) ? $params['source']['class'] : NativeArraySource::class;
            if (isset($params['di'])) {
                $params['source']['di'] = $params['di'];
            }
            $this->source = new $class(isset($params['source']) ? $params['source'] : NULL);
            $this->translationsScope = isset($params['translationsScope']) ? $params['translationsScope'] : 'translation';
            $this->ttl = isset($params['ttl']) ? $params['ttl'] : 10 * 60;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function makeAdapter($language)
    {
        return new ApcuAdapterWrapper([
            'language' => $language,
            'scope' => $this->translationsScope,
            'adapter' => $this->source->makeAdapter($language),
            'ttl' => $this->ttl
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function add($language, $label, $translation)
    {
        if ($this->source->add($language, $label, $translation)) {
            $key = (!empty($this->translationsScope) ? $this->translationsScope . '.' : '') . $language . '.' . $label; 
            return apcu_store($key, $translation, $this->ttl);
        }
        return false;
    }
    
    public function clean()
    {
        if (class_exists('\APCUIterator')) {
            $iterator = new \APCUIterator('/^' . $this->translationsScope . '\./');
        } else {
            $iterator = new \APCIterator('user', '/^' . $this->translationsScope . '\./');
        }
        apcu_delete($iterator);
    }
    
    /**
     * @return AbstractSource
     */
    function getSource()
    {
        return $this->source;
    }

    /**
     * @param AbstractSource $source
     * @return ApcuSourceWrapper
     */
    function setSource(AbstractSource $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    function getTranslationsScope()
    {
        return $this->translationsScope;
    }

    /**
     * @param string $translationsScope
     * @return ApcuSourceWrapper
     */
    function setTranslationsScope($translationsScope)
    {
        $this->translationsScope = $translationsScope;
        return $this;
    }

    /**
     * @return int
     */
    function getTtl()
    {
        return $this->ttl;
    }
    
    /**
     * @param int $ttl
     * @return ApcuSourceWrapper
     */
    function setTtl($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }
}
