<?php

namespace Core\Event;

class Event implements EventInterface
{
    private $name;
    private $propagationStopped = false;
    private $data;

    public function __construct($name, $data = null)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}