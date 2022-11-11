<?php

namespace WebApp\Services\ToasterService\Toasts;


class ErrorToast extends Toast
{

    public function getClass(): string
    {
        return "deep-orange-text";
    }
}