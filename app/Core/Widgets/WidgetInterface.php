<?php

namespace Core\Widgets;

interface WidgetInterface
{
    public function getId(): string;
    public function getTitle(): string;
    public function getDescription(): string;
    public function getIcon(): string;
    public function getSize(): string; // 'small', 'medium', 'large'
    public function render(): string;
    public function getSettings(): array;
}