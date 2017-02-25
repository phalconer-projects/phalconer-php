<?php

namespace phalconer\user\controller;

use phalconer\common\controller\BaseController;

class UserController extends BaseController
{
    /**
     * {@inheritdoc}
     * 
     * @return array
     */
    protected function access()
    {
        return [
            [
                'roles' => ['guest'],
                'actions' => ['index'],
                'allow' => true
            ]
        ];
    }
    
    public function indexAction()
    {
        return 'user';
    }
    
    public function loginAction()
    {
        return 'login';
    }
}
