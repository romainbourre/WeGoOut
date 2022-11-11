<?php

namespace WebApp\Services\ToasterService\Toasts;

class SuccessToast extends Toast
{

    public function getClass(): string
    {
        return 'lime-text text-accent-3';
    }
}