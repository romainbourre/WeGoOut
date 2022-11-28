<?php

namespace WebApp\Services\ToasterService\Toasts;

class WarningToast extends Toast
{

    public function getClass(): string
    {
        return "amber-text text-accent-3";
    }
}