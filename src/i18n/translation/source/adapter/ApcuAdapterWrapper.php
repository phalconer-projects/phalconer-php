<?php

namespace phalconer\i18n\translation\source\adapter;

use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Adapter;
use Phalcon\Translate\Exception;

class ApcuAdapterWrapper extends Adapter implements AdapterInterface, \ArrayAccess
{
    /**
     * @var AdapterInterface 
     */
    protected $adapter;
    
    /**
     * @var string
     */
    protected $prefix;
    
    /**
     * @var string
     */
    protected $language;
    
    /**
     * @var int
     */
    protected $ttl = 0;
    
    /**
     * @param array $options
     * @throws \Phalcon\Translate\Exception
     */
    public function __construct(array $options)
    {
        if (!isset($options['adapter'])) {
            throw new Exception("Parameter 'adapter' is required");
        }
        if (!($options['adapter'] instanceof \ArrayAccess)) {
            throw new Exception("Parameter 'adapter' must be implement ArrayAccess interface");
        }
        if (!isset($options['language'])) {
            throw new Exception("Parameter 'language' is required");
        }
        $this->adapter = $options['adapter'];
        $this->language = $options['language'];
        $this->prefix = (!empty($options['scope']) ? $options['scope'] . '.' : '') . $this->language . '.';
        if (isset($options['ttl'])) {
            $this->ttl = $options['ttl'];
        }
    }
    
    /**
     * {@inheritdoc}
     *
     * @param  string  $translateKey
     * @return boolean
     */
    public function exists($translateKey)
    {
        $key = $this->getKey($translateKey);
        if (!apcu_exists($key)) {
            return $this->adapter->exist($translateKey);
        }
        return true;
    }
    
    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @param  array  $placeholders
     * @return string
     */
    public function query($translateKey, $placeholders = null)
    {
        $translation = $this->getTranslation($translateKey);
//        print_r([$translateKey, $translation]);
        if (is_array($placeholders)) {
            foreach ($placeholders as $key => $value) {
                $translation = str_replace('%' . $key . '%', $value, $translation);
            }
        }
        return $translation;
    }
    
    /**
     * Adds a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function add($translateKey, $message)
    {
        $key = $this->getKey($translateKey);
        if (!$this->adapter->offsetSet($translateKey, $message)) {
            return false;
        }
        return apcu_store($key, $message, $this->ttl);
    }
    
    /**
     * Update a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function update($translateKey, $message)
    {
        return $this->add($translateKey, $message);
    }
    
    /**
     * Deletes a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @return boolean
     */
    public function delete($translateKey)
    {
        $key = $this->getKey($translateKey);
        apcu_delete($key);
        return $this->adapter->offsetUnset($translateKey);
    }
    
    /**
     * Sets (insert or updates) a translation for given key
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function set($translateKey, $message)
    {
        return $this->exists($translateKey) ?
            $this->update($translateKey, $message) : $this->add($translateKey, $message);
    }
    
    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @return string
     */
    public function offsetExists($translateKey)
    {
        return $this->exists($translateKey);
    }
    
    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @param  string $message
     * @return string
     */
    public function offsetSet($translateKey, $message)
    {
        return $this->update($translateKey, $message);
    }
    
    /**
     * {@inheritdoc}
     *
     * @param string $translateKey
     * @return string
     */
    public function offsetGet($translateKey)
    {
        return $this->query($translateKey);
    }
    
    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @return string
     */
    public function offsetUnset($translateKey)
    {
        return $this->delete($translateKey);
    }
    
    /**
     * Get translation from APCu or source adapter.
     *
     * @param string $label
     * @return string translation
     */
    protected function getTranslation($label)
    {
        $key = $this->getKey($label);
        if (apcu_exists($key)) {
            $translation = apcu_fetch($key);
        } else {
            $translation = $this->adapter->query($label);
            apcu_store($key, $translation, $this->ttl);
        }
        return $translation;
    }
    
    /**
     * Returns key for label.
     *
     * @param  string $label
     * @return string
     */
    protected function getKey($label)
    {
        return $this->prefix . $label;
    }
}
