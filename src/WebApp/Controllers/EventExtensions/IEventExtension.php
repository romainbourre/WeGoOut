<?php


namespace WebApp\Controllers\EventExtensions;


interface IEventExtension
{
    public function getExtensionName(): string;

    public function getTabPosition(): int;

    public function getContent(): string;

    public function isActivated(): bool;
}