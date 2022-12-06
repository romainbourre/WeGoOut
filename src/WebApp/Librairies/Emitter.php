<?php

namespace WebApp\Librairies;

use Exception;


class Emitter
{
    private static ?Emitter $_instance = null;
    public array $listeners = [];

    public function emit(string $event, ...$args): void
    {
        if ($this->hasListener($event)) {
            foreach ($this->listeners[$event] as $priority) {
                foreach ($priority as $listener) {
                    call_user_func_array($listener, $args);
                }
            }
        } else {
            throw new class($event) extends Exception {
                public function __construct(string $event)
                {
                    parent::__construct(__CLASS__ . " : unknown listener \"$event\"");
                }
            };
        }
    }

    public function on(string $event, callable $callable, int $priority = null): void
    {
        if (!$this->hasListener($event)) {
            $this->listeners[$event] = [];
        }

        if (!is_null($priority) && $priority >= 1 && $priority <= 100) {
            $this->listeners[$event][$priority][] = $callable;
        } elseif (is_null($priority)) {
            $this->listeners[$event][100][] = $callable;
        }
    }


    public static function getInstance(): Emitter
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function hasListener(string $event): bool
    {
        return array_key_exists($event, $this->listeners);
    }
}
