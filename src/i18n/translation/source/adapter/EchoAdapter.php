<?php

namespace phalconer\i18n\translation\source\adapter;

use Phalcon\Translate\AdapterInterface;

class EchoAdapter implements AdapterInterface
{
    public function t($translateKey, $placeholders = null)
    {
        $translation = $translateKey;
        if (is_array($placeholders)) {
            foreach ($placeholders as $key => $value) {
                $translation = str_replace('%' . $key . '%', $value, $translation);
            }
        }
        return $translation;
    }
    
    public function _($translateKey, $placeholders = null)
    {
        return $this->t($index, $placeholders);
    }

    public function query($index, $placeholders = null)
    {
        return $this->t($index, $placeholders);
    }
    
    public function exists($index)
    {
        return true;
    }
}
