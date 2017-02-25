<?php

namespace phalconer\common\controller;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl;

class BaseController extends Controller
{
    public static $defaultRole = 'guest';
    
    public static $acessErrorRedirect = 'user/login';
    
    public static $accessErrorMessage = 'You do not have permission to access this area';
    
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
    
    /**
     * Triggers before a route is successfully executed
     *
     * @param  Dispatcher $dispatcher
     *
     * @return boolean|void
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        // Get the current role
        $accessRole = $this->session->get('role');
        if (!$accessRole) {
            $accessRole = static::$defaultRole;
        }

        // Get the current Controller/Action from the dispatcher
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        // Create the ACL Rule List
        $acl = $this->makeAcl($controller);

        // See if they have permission
        $allowed = $acl->isAllowed($accessRole, $controller, $action);
        if ($allowed != Acl::ALLOW)
        {
            if (static::$accessErrorMessage) {
                $this->flash->error(static::$accessErrorMessage);
            }
            if (static::$acessErrorRedirect) {
                $this->response->redirect(static::$acessErrorRedirect);
            }

            // Stop the dispatcher at the current operation
            if (isset($this->view)) {
                $this->view->disable();
            }
            return false;
        }
    }
    
    protected function makeAcl($controller)
    {
        $acl = new \Phalcon\Acl\Adapter\Memory();
        $acl->addResource(new \Phalcon\Acl\Resource($controller), $this->getActions());
        foreach ($this->access() as $accessItem) {
            $method = $accessItem['allow'] ? 'allow' : 'deny';
            foreach ($accessItem['roles'] as $role) {
                if (!$acl->isRole($role)) {
                    $acl->addRole($role);
                }
                $acl->$method($role, $controller, $accessItem['actions']);
            }
        }
        return $acl;
    }
    
    protected function getActions()
    {
        $actions = [];
        foreach ($this->access() as $accessItem) {
            foreach ($accessItem['actions'] as $action) {
                if (!in_array($action, $actions)) {
                    $actions[] = $action;
                }
            }
        }
        return $actions;
    }
}
