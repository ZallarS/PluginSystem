<?php

namespace Core\Event;

interface EventSubscriber
{
    public static function getSubscribedEvents();
}