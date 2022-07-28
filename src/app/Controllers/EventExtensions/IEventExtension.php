<?php


namespace App\Controllers\EventExtensions;


interface IEventExtension
{
    public function getExtensionName(): string;
    public function getTabPosition(): int;
    public function getContent(): string;
    public function getAjaxSwitch(string $action);
    public function isActivated(): bool;
}