<?php

namespace WebApp\Services\ToasterService;

use PhpLinq\PhpLinq;
use WebApp\Services\ToasterService\Toasts\ErrorToast;
use WebApp\Services\ToasterService\Toasts\SuccessToast;
use WebApp\Services\ToasterService\Toasts\Toast;
use WebApp\Services\ToasterService\Toasts\WarningToast;

class ToasterService implements ToasterInterface, ToasterRepositoryInterface
{
    private const TOASTER_KEY = 'TOASTS_REPOSITORY';

    public function success(string $message): Toast
    {
        $toast = new SuccessToast($message);
        return $this->saveToast($toast);
    }

    private function saveToast(Toast $toast): Toast
    {
        $_SESSION[self::TOASTER_KEY][] = $toast;
        return $toast;
    }

    public function warning(string $message): Toast
    {
        $toast = new WarningToast($message);
        return $this->saveToast($toast);
    }

    public function error(string $message): Toast
    {
        $toast = new ErrorToast($message);
        return $this->saveToast($toast);
    }

    public function getToasts(): array
    {
        if (!isset($_SESSION[self::TOASTER_KEY])) {
            return [];
        }
        $toasts = new PhpLinq();
        foreach ($_SESSION[self::TOASTER_KEY] as $key => $toast) {
            $toasts->add($toast);
            unset($_SESSION[self::TOASTER_KEY][$key]);
        }
        return $toasts->toArray();
    }
}