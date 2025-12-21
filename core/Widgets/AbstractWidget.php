<?php

namespace Core\Widgets;

abstract class AbstractWidget implements WidgetInterface
{
    protected $id;
    protected $title;
    protected $description;
    protected $icon;
    protected $size = 'medium';
    protected $settings = [];

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    abstract public function render(): string;
}