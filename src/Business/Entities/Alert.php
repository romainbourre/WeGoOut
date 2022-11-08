<?php

namespace Business\Entities
{

    /**
     * Class Alert
     * Manage alert in the application
     */
    class Alert
    {

        /**
         * Name of SESSION variable where ara backup alerts
         */
        private const CONF_ALERTS_SESSION = "ALERTS_PILE";
        /**
         * Timer for alert displaying
         */
        private const CONF_ALERTS_TIMER = 5000;

        private $message;
        private $level;
        private $error;
        private $action;

        /**
         * Alert constructor.
         * @param string $message
         * @param int $level
         * @param int|null $error
         * @param string|null $action
         */
        private function __construct(string $message, int $level = 0, int $error = null, string $action = null)
        {
            $this->message = $message;
            $this->level = $level;
            $this->error = $error;
            $this->action = $action;
        }

        /**
         * Save a new alert in SESSION variable
         * @param string $message message of alert
         * @param int $level level of alert 0 : neutral | 1 : valid | 2 : Warning | 3 : Fatal
         * @param int|null $error number of the alert
         * @param string|null $action link for the alert action
         */
        public static function addAlert(string $message, int $level = 0, int $error = null, string $action = null): void
        {
            $_SESSION[self::CONF_ALERTS_SESSION][] = new Alert($message, $level, $error, $action);
        }

        /**
         * Get view of alert for the user
         * @return string view
         */
        private function getView(): string
        {
            // COMPOSE STYLE OF ERROR LEVEL
            $class = "";
            switch ($this->level)
            {
                case 0:
                    $class = "";
                    break;
                case 1:
                    $class = "lime-text text-accent-3";
                    break;
                case 2:
                    $class = "amber-text text-accent-3";
                    break;
                case 3:
                    $class = "deep-orange-text";
                    break;
            }
            // COMPOSE ACTION IF EXIST
            if (!is_null($this->action))
            {
                $toastContent = "<span>" . $this->message . "</span><a href=\"" . $this->action . "\" class=\"btn-flat toast-action\">Undo</a>";
            }
            else
            {
                $toastContent = $this->message;
            }
            return '<script>Materialize.toast("' . $toastContent . '",' . self::CONF_ALERTS_TIMER . ',"' . $class . '")</script>';
        }

        /**
         * Read view of the alert
         */
        public function readAlert(): void
        {
            echo $this->getView();
        }

        /**
         * Load and read alerts of pile and delete her of the pile after
         */
        public static function autoReadAlerts(): void
        {
            if (isset($_SESSION[self::CONF_ALERTS_SESSION]) && !empty($_SESSION[self::CONF_ALERTS_SESSION]))
            {
                foreach ($_SESSION[self::CONF_ALERTS_SESSION] as $key => $alert)
                {
                    if (is_a($alert, 'Domain\Entities\Alert'))
                    {
                        $alert->readAlert();
                        unset($_SESSION[self::CONF_ALERTS_SESSION][$key]);
                    }
                }
            }
        }

        /**
         * Get the pile of alerts
         * @return array|null piles
         */
        public static function loadAlerts(): ?array
        {
            if (isset($_SESSION[self::CONF_ALERTS_SESSION]) && !empty($_SESSION[self::CONF_ALERTS_SESSION]))
            {
                return $_SESSION[self::CONF_ALERTS_SESSION];
            }
            return null;
        }

        /**
         * Get message of the alert
         * @return string
         */
        public function getMessage(): string
        {
            return $this->message;
        }

        /**
         * Get level of the alert
         * @return int
         */
        public function getLevel(): int
        {
            return $this->level;
        }

        /**
         * Get lnumber of the alert
         * @return int
         */
        public function getError(): int
        {
            return $this->error;
        }

        /**
         * Get action link of the alert
         * @return string
         */
        public function getAction(): string
        {
            return $this->action;
        }

    }
}