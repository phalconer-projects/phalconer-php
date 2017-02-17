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
use phalconer\Application;
use phalconer\user\controller\UserController;

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
     * @var Exception
     */
    private $exception;

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
                'url',
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
    }

    /**
     * @Given this user with name :name and password :pass
     */
    public function thisUserWithNameAndPassword($name, $pass)
    {
        throw new PendingException();
    }

    /**
     * @Given this service with URI :arg1 and access permissions :arg2
     */
    public function thisServiceWithUriAndAccessPermissions($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @When I request the :arg1 service
     */
    public function iRequestTheService($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then I see login form
     */
    public function iSeeLoginForm()
    {
        throw new PendingException();
    }

    /**
     * @When I send login data with name :arg1 and password :arg2
     */
    public function iSendLoginDataWithNameAndPassword($arg1, $arg2)
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
