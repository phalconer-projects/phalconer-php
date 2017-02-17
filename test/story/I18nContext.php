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
use phalconer\i18n\LanguageConfig;
use phalconer\i18n\translation\Translator;
use phalconer\i18n\translation\source\AbstractSource;
use phalconer\i18n\translation\source\NativeArraySource;
use phalconer\i18n\translation\source\DatabaseSource;
use phalconer\i18n\translation\source\ApcuSourceWrapper;

class TestController extends Controller
{
    public $label;
    
    public function indexAction()
    {
        if (!empty($this->label)) {
            return $this->getDI()->get('translator')->getAdapter()->_($this->label);
        }
    }
}

/**
 * Defines application features from the specific context.
 */
class I18nContext extends TestCase implements Context
{
    use \transform\CastStringToArray;
    use \transform\CastStringToEmpty;
    
    /**
     * @var Application
     */
    private $app;
    
    /**
     * @var Translator
     */
    private $translator;
    
    /**
     * @var Exception
     */
    private $exception;
    
    /**
     * @var string
     */
    private $dir;
    
    /**
     * @var string
     */
    private $text;
    
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
                'url' => [
                    'class' => '\phalconer\i18n\Url'
                ],
                'router' => [
                    'routes' => LanguageConfig::$routes
                ],
                'db' => [
                    'driver'   => 'mysql',
                    'host'     => 'localhost',
                    'dbname'   => 'phalconer_test',
                    'username' => 'phalconer_test',
                    'password' => ''
                ]
            ],
            'i18n' => [
                'translator' => [
//                    'supportedLanguages' => ['en', 'ru'],
//                    'defaultLanguage' => 'en',
//                    'defaultSourceName' => 'app',
                    'sources' => [
                        'app' => [
                            'class' => NativeArraySource::class
                        ]
                    ]
                ]
            ]
        ];
        $this->app = new Application(new \Phalcon\Config($config));
        $this->app->getDI()->set(
            'IndexController',
            function () {
                return new TestController();
            }
        );
        $this->app->getApplication()->useImplicitView(false);
        $this->translator = $this->app->getTranslator();
        $this->translator->registerRedirectDispatcherEvent();
    }

    /**
     * @Given there list :langs as supported languages
     */
    public function thereListAsSupportedLanguages($langs)
    {
        $this->assertTrue(is_array($langs));
        $this->translator->setSupportedLanguages($langs);
        $this->assertEquals($langs, $this->translator->getSupportedLanguages());
    }

    /**
     * @Given the :defaultLang as default language
     */
    public function theAsDefaultLanguage($defaultLang)
    {
        $this->translator->setDefaultLanguage($defaultLang);
    }
    
    /**
     * @When I setup the :defaultLang as default language
     */
    public function iSetupTheAsDefaultLanguage($defaultLang)
    {
        try {
            $this->translator->setDefaultLanguage($defaultLang);
        } catch (Exception $exp) {
            $this->exception = $exp;
        }
    }

    /**
     * @When I request the :wontedLang as current language
     */
    public function iSelectTheAsCurrentLanguage($wontedLang)
    {
        $this->translator->setupLanguage($wontedLang);
    }

    /**
     * @Then I should have :setupLang as current language
     */
    public function iShouldHaveAsCurrentLanguage($setupLang)
    {
        $this->assertEquals($setupLang, $this->translator->getLanguageFromSession());
    }

    /**
     * @Then I should have :defaultLang as default language
     */
    public function iShouldHaveAsDefaultLanguage($defaultLang)
    {
        $this->assertEquals($defaultLang, $this->translator->getDefaultLanguage());
    }

    /**
     * @Then I should have :message error
     */
    public function iShouldHaveError($message)
    {
        $this->assertEquals($message, $this->exception->getMessage());
    }

    /**
     * @Given this :string as accepted languages
     */
    public function thisAsAcceptedLanguages($string)
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $string;
    }

    /**
     * @When I request the best language as current language
     */
    public function iRequestTheBestLanguageAsCurrentLanguage()
    {
        $bestLanguage = $this->translator->getBestLanguage();
        $this->translator->setupLanguage($bestLanguage);
    }

    /**
     * @Given a file named :name with:
     */
    public function aFileNamedWith($name, PyStringNode $stringNode)
    {
        if (empty($this->dir)) {
            $this->dir = sys_get_temp_dir() . '/i18n_context';
            if (!is_dir($this->dir)) {
                mkdir($this->dir);
            }
        }
        $file = $this->dir . "/$name";
        if (is_file($file)) {
            unlink($file);
        }
        file_put_contents($file, $stringNode->getRaw());
    }

    /**
     * @Given I setup messages dir
     */
    public function iSetupMessagesDir()
    {
        $this->translator->getSource('app')->setMessagesDir($this->dir);
    }
    
    /**
     * @When I setup messages with:
     */
    public function iSetupMessagesWith(PyStringNode $stringNode)
    {
        $messages = json_decode($stringNode->getRaw(), true);
        $this->translator->getSource('app')->setMessages($messages);
    }

    /**
     * @When I get translate :label sentence
     */
    public function iGetTranslateSentence($label)
    {
        $this->assertTrue($this->translator->canTranslate($this->translator->getLanguageFromSession()));
        $adapter = $this->translator->getAdapter();
        $this->assertTrue($adapter instanceof \Phalcon\Translate\Adapter);
        $this->text = $adapter->_($label);
    }

    /**
     * @Then I should have translated :text
     */
    public function iShouldHaveTranslated($text)
    {
        $this->assertEquals($text, $this->text);
    }

    /**
     * @Given this :uri as some service URI
     */
    public function thisAsSomeServiceUri($uri)
    {
        $this->app->getDI()->set(
            ucfirst($uri) . 'Controller',
            function () {
                return new TestController();
            }
        );
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
     * @Given the :source as translation source
     */
    public function theAsTranslationAdapter($source)
    {
        if ($source === 'array') {
            $options = ['class' => NativeArraySource::class];
        }
        if ($source === 'database') {
            $options = ['class' => DatabaseSource::class];
            $this->app->getDI()->get('db')->delete('translation');
        }
        if ($source === 'apcu_array') {
            $options = [
                'class' => ApcuSourceWrapper::class,
                'ttl' => 1,
                'source' => [
                    'class' => NativeArraySource::class,
                ]
            ];
            apcu_clear_cache();
        }
        if ($source === 'apcu_db') {
            $options = [
                'class' => ApcuSourceWrapper::class,
                'ttl' => 1,
                'source' => [
                    'class' => DatabaseSource::class
                ]
            ];
            $this->app->getDI()->get('db')->delete('translation');
            apcu_clear_cache();
        }
        if (!isset($options)) {
            throw new Exception("Unsupported source: $source");
        }
        
        $this->translator = new Translator($this->app->getDI());
        $this->translator->initSourcesList(['app' => $options]);
        $this->translator->setDefaultSourceName('app');
        $this->app->setTranslator($this->translator);
        $this->translator->registerRedirectDispatcherEvent();
    }
    
    /**
     * @Given the translation of :text text to the :lang language as :translation
     */
    public function theTranslationOfTextToTheLanguageAs($text, $lang, $translation)
    {
        $this->translator->add($lang, $text, $translation);
    }
    
    /**
     * @Given this :uri as service URI with translation message :text
     */
    public function thisAsServiceUriWithTranslationMessage($uri, $text)
    {
        $this->app->getDI()->set(
            ucfirst($uri) . 'Controller',
            function () use ($text) {
                $controller = new TestController();
                $controller->label = $text;
                return $controller;
            }
        );
    }
    
    /**
     * @Then I see :text in response
     */
    public function iSeeInResponse($text)
    {
        $response = $this->app->getDI()->get('response');
        $this->assertEquals($text, $response->getContent());
    }

    /**
     * @Then I should have convert URL from :sourceUrl to :resultUrl
     */
    public function iShouldHaveConvertUrlFromTo($sourceUrl, $resultUrl)
    {
        $url = $this->app->getDI()->get('url');
        $this->assertEquals($resultUrl, $url->get($sourceUrl));
    }

    /**
     * @When I change translation of :label text on :language language to :translation in APCu source
     */
    public function iChangeTranslationOfTextOnLanguageToInApcuSource($label, $language, $translation)
    {
        /** @var ApcuSourceWrapper $apcuWrapper */
        $apcuWrapper = $this->translator->getSource();
        $this->assertTrue($apcuWrapper instanceof ApcuSourceWrapper, "Source is not ApcuSourceWrapper instance");
        
        /** @var AbstractSource $source */
        $source = $apcuWrapper->getSource();
        $this->assertTrue($source instanceof AbstractSource, "APCu source is not AbstractSource instance");
        $this->assertTrue($source->add($language, $label, $translation));
    }

    /**
     * @When I clean APCu wrapper
     */
    public function iCleanAPCuWrapper()
    {
        /** @var ApcuSourceWrapper $apcuWrapper */
        $apcuWrapper = $this->translator->getSource();
        $this->assertTrue($apcuWrapper instanceof ApcuSourceWrapper, "Source is not ApcuSourceWrapper instance");
        $apcuWrapper->clean();
    }

    /**
     * @When I change translation of :label text on :language language to :translation
     */
    public function iChangeTranslationOfTextOnLanguageTo($label, $language, $translation)
    {
        /** @var AbstractSource $source */
        $source = $this->translator->getSource();
        $this->assertTrue($source instanceof AbstractSource, "APCu source is not AbstractSource instance");
        $this->assertTrue($source->add($language, $label, $translation));
    }
}
