<?php

namespace story;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\TestCase;
use Exception;
use Phalcon\Mvc\Controller;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\DiInterface;
use phalconer\Application;
use phalconer\user\controller\UserController;
use phalconer\user\model\User;
use phalconer\common\controller\BaseController;

class CommonUserTestController extends BaseController
{
    public $access = [
        [
            'roles' => ['guest'],
            'actions' => ['index'],
            'allow' => false
        ]
    ];
    
    public function allowAccess($roles)
    {
        $this->access[] = [
            'roles' => $roles,
            'actions' => ['index'],
            'allow' => true
        ];
    }
    
    protected function access()
    {
        return $this->access;
    }
    
    public function indexAction()
    {
        return 'test';
    }
}

/**
 * Defines application features from the specific context.
 */
class UserContext extends TestCase implements Context
{
    use \transform\CastStringToArray;
    use \transform\CastStringToEmpty;
    
    /**
     * @var Application
     */
    private $app;
    
    /**
     * @var DiInterface
     */
    private $di;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $config = [
            'services' => [
                'session',
                'crypt' => [
                    'key' => 'testKey'
                ],
                'security',
                'url',
                'flash' => [
                    'class' => \Phalcon\Flash\Session::class
                ],
                'router',
                'db' => [
                    'driver'   => 'mysql',
                    'host'     => 'localhost',
                    'dbname'   => 'phalconer_test',
                    'username' => 'phalconer_test',
                    'password' => ''
                ]
            ],
        ];
        $this->app = new Application(new \Phalcon\Config($config));
        $this->app->getDI()->set(
            'UserController',
            function () {
                return new UserController();
            }
        );
        $this->app->getApplication()->useImplicitView(false);
        $this->di = $this->app->getDI();
    }

    /**
     * @Given this user with name :name and password :pass
     */
    public function thisUserWithNameAndPassword($name, $pass)
    {
        $count = User::count(['name = :name:', 'bind' => ['name' => $name]]);
        if ($count > 0) {
            $user = User::findFirst(['name = :name:', 'bind' => ['name' => $name]]);
        } else {
            $user = new User();
            $user->name = $name;
        }
        $user->password_hash = $this->di->get('security')->hash($pass);
        $this->assertTrue($user->save(), "Can't save user");
    }

    /**
     * @Given the :uri service with access roles :roles
     */
    public function theServiceWithAccessRoles($uri, $roles)
    {
        $this->app->getDI()->set(
            ucfirst($uri) . 'Controller',
            function () use ($roles) {
                $controller = new CommonUserTestController();
                $controller->allowAccess($roles);
                return $controller;
            }
        );
        $this->assertTrue($this->app->getDI()->get(ucfirst($uri) . 'Controller') instanceof BaseController);
    }

    private function setupUri($uri)
    {
        $_SERVER['REQUEST_URI'] = $uri;
        $_GET['_url'] = strlen($uri) > 1 ? $uri : '';
    }
    
    /**
     * @When I go to the :uri URI
     */
    public function iGoToTheUri($uri)
    {
        $this->setupUri($uri);
        $this->app->run();
    }

    /**
     * @Then I see current URI equals :uri
     */
    public function iSeeCurrentUriEquals($uri)
    {
        $response = $this->app->getDI()->get('response');
        if ($response->getStatusCode() === '302 Found') {
            $location = $response->getHeaders()->get('Location');
            $this->iGoToTheUri($location);
        }
        $this->assertEquals(
                rtrim($uri, '/'),
                rtrim($this->app->getDI()->get('router')->getRewriteUri(), '/')
        );
    }

    /**
     * @Then I see login form
     */
    public function iSeeLoginForm()
    {
        $response = $this->app->getDI()->get('response');
        if ($response->getStatusCode() === '302 Found') {
            $location = $response->getHeaders()->get('Location');
            $this->iGoToTheUri($location);
        }
        $this->assertEquals('login', $response->getContent());
    }

    /**
     * @When I send login data with name :name and password :password
     */
    public function iSendLoginDataWithNameAndPassword($name, $password)
    {
        throw new PendingException();
    }

    /**
     * @Then I see login form with message :arg1
     */
    public function iSeeLoginFormWithMessage($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then I see :arg1 service output
     */
    public function iSeeServiceOutput($arg1)
    {
        throw new PendingException();
    }
}
