<?php

namespace WebApp\Services\ToasterService;

use WebApp\Services\ToasterService\Toasts\Toast;

interface ToasterInterface
{
    public function success(string $message): Toast;

    public function warning(string $message): Toast;

    public function error(string $message): Toast;
}