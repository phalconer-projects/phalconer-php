<?php

namespace phalconer\i18n;

class LanguageConfig
{
    public static $routes = [
        '/{language:[a-z]{2}}' => [[
            'controller' => 'index',
            'action' => 'index'
        ]],
        '/{language:[a-z]{2}}/:controller' => [[
            'controller' => 2,
            'action' => 'index'
        ]],
        '/{language:[a-z]{2}}/:controller/:action' => [[
            'controller' => 2,
            'action' => 3
        ]],
        '/{language:[a-z]{2}}/:controller/:action/:params' => [[
            'controller' => 2,
            'action' => 3,
            'params' => 4
        ]]
    ];
}
