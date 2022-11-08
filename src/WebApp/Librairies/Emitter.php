<?php

namespace WebApp\Librairies
{

    use Exception;

    /**
     * Class Emitter
     * Emitter class of the web application
     * @package App\Lib
     */
    class Emitter
    {

        private static ?Emitter $_instance = null;
        public array $listeners = [];

        /**
         * Emit action
         * @param string $event
         * @param ...$args
         * @return bool
         * @throws Exception
         */
        public function emit(string $event, ...$args): bool
        {

            if ($this->hasListener($event))
            {

                foreach ($this->listeners[$event] as $priority)
                {

                    foreach ($priority as $listener)
                    {

                        call_user_func_array($listener, $args);

                        return true;

                    }

                }

            }
            else
            {

                throw new class($event) extends Exception
                {
                    public function __construct(string $event)
                    {
                        parent::__construct(__CLASS__ . " : unknown listener \"$event\"");
                    }
                };

            }

            return false;

        }

        /**
         * Set action
         * @param string $event
         * @param callable $callable
         * @param int|null $priority
         */
        public function on(string $event, callable $callable, int $priority = null)
        {

            if (!$this->hasListener($event))
            {
                $this->listeners[$event] = [];
            }

            if (!is_null($priority) && $priority >= 1 && $priority <= 100)
            {

                $this->listeners[$event][$priority][] = $callable;

            }
            else if (is_null($priority))
            {

                $this->listeners[$event][100][] = $callable;

            }

        }

        /**
         * @return Emitter instance
         */
        public static function getInstance(): Emitter
        {
            if (!self::$_instance)
            {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Check if action is set
         * @param string $event
         * @return bool
         */
        private function hasListener(string $event): bool
        {
            return array_key_exists($event, $this->listeners);
        }

    }
}