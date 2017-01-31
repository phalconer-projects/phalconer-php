<?php

namespace spec;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\TestCase;

/**
 * Defines application features from the specific context.
 */
class StackContext extends TestCase implements Context
{
    use \transform\CastStringToArray;
    
    private $stack;
    
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->stack = new Stack();
    }
    
    /**
     * @When a new stack is created
     */
    public function aNewStackIsCreated()
    {
        $this->stack = new Stack();
    }

    /**
     * @Then it is empty
     */
    public function itIsEmpty()
    {
        $this->assertEquals(0, $this->stack->size());
        $this->assertTrue($this->stack->isEmpty());
    }

    /**
     * @When element :a is added to the stack
     */
    public function elementIsAddedToTheStack($a)
    {
        $this->stack->push($a);
    }

    /**
     * @Then element :a is at the top of the stack
     */
    public function elementIsAtTheTopOfTheStack($a)
    {
        $this->assertEquals($a, $this->stack->peek());
    }

    /**
     * @When a stack has :elements
     */
    public function aStackHas($elements)
    {
        foreach ($elements as $element) {
            $this->stack->push($element);
        }
    }

    /**
     * @Then a pop operation returns :topElement
     */
    public function aPopOperationReturns($topElement)
    {
        $this->assertEquals($topElement, $this->stack->pop());
    }

    /**
     * @Then the size of the stack is :size
     */
    public function theSizeOfTheStackIs($size)
    {
        $this->assertEquals($size, $this->stack->size());
    }
}
