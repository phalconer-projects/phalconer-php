<?php

namespace story;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\TestCase;
use phalconer\Application;
use Exception;
use phalconer\i18n\AbstractTranslator;

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
     * @var AbstractTranslator
     */
    private $translator;
    
    /**
     * @var string
     */
    private $setupLang;
    
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
        $config = [];
        $this->app = new Application(new \Phalcon\Config($config));
        $this->translator = $this->app->getTranslator();
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
        $this->setupLang = $this->translator->getLanguage($wontedLang);
    }

    /**
     * @Then I should have :setupLang as current language
     */
    public function iShouldHaveAsCurrentLanguage($setupLang)
    {
        $this->assertEquals($setupLang, $this->setupLang);
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
        $this->setupLang = $this->translator->getLanguage($bestLanguage);
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
        $this->translator->setMessagesDir($this->dir);
    }
    
    /**
     * @When I setup messages with:
     */
    public function iSetupMessagesArray(PyStringNode $stringNode)
    {
        $messages = json_decode($stringNode->getRaw(), true);
        $this->translator->setMessages($messages);
    }

    /**
     * @When I get translate :label sentence
     */
    public function iGetTranslateSentence($label)
    {
        $this->assertTrue($this->translator->canTranslate($this->setupLang));
        $translation = $this->translator->getTranslationAdapter($this->setupLang);
        $this->assertTrue($translation instanceof \Phalcon\Translate\Adapter);
        $this->text = $translation->_($label);
    }

    /**
     * @Then I should have translated :text
     */
    public function iShouldHaveTranslated($text)
    {
        $this->assertEquals($text, $this->text);
    }
}
