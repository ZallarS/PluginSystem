<?php

namespace Core\Event;

interface EventInterface
{
    public function getName();
    public function isPropagationStopped();
    public function stopPropagation();
    public function getData();
    public function setData($data);
}