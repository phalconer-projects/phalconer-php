<?php

namespace spec;

class Stack
{
    private $container = [];
    
    public function size()
    {
        return count($this->container);
    }
    
    public function isEmpty()
    {
        return $this->size() === 0;
    }
    
    public function push($element)
    {
        $this->container[] = $element;
    }
    
    public function peek()
    {
        return $this->container[$this->size() - 1];
    }
    
    public function pop()
    {
        return array_pop($this->container);
    }
}
