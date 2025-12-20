<?php

namespace Core\Session;

class FlashMessage
{
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function set($key, $message)
    {
        $this->session->set('flash_' . $key, $message);
    }

    public function get($key, $default = null)
    {
        $message = $this->session->get('flash_' . $key, $default);
        $this->session->remove('flash_' . $key);
        return $message;
    }

    public function has($key)
    {
        return $this->session->has('flash_' . $key);
    }
}