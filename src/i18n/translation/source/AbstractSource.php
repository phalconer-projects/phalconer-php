<?php

namespace phalconer\i18n\translation\source;

use Phalcon\Annotations\Extended\AdapterInterface;

abstract class AbstractSource
{
    /**
     * @param array $params
     */
    public function __construct(array $params = NULL)
    {
    }
    
    /**
     * @param string $language
     * @return AdapterInterface
     */
    public abstract function makeAdapter($language);
    
    /**
     * @param string $language
     * @param string $label
     * @param string $translation
     */
    public abstract function add($language, $label, $translation);
}
