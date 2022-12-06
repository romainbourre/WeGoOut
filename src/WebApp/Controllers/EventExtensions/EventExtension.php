<?php

namespace WebApp\Controllers\EventExtensions
{


    use System\Routing\Responses\Response;
    use WebApp\Controllers\AppController;

    abstract class EventExtension extends AppController
    {
        public function __construct(public readonly string $extensionId, public readonly string $extensionName, public readonly int $order)
        {
            parent::__construct();
            $this->autoloaderCSS();
            $this->autoloaderAjax();
            $this->autoloaderJS();
        }

        abstract public function computeActionQuery(string $action): Response;

        abstract public function getContent(): string;

        public function isActivated(): bool
        {
            return true;
        }

        public function autoloaderCSS(): void
        {
            $this->addCssStyle("css-{$this->extensionId}.css");
        }

        public function autoloaderJS(): void
        {
            $this->addJsScript("tab-{$this->extensionId}.js");
        }

        public function autoloaderAjax(): void
        {
            $this->addJsScript("ajax-{$this->extensionId}.js");
        }

        protected function render(string $view, array $variables = null): string
        {
            ob_start();
            if (!is_null($variables)) extract($variables);
            $path = APP . "/Controllers/EventExtensions/Views/tab-{$this->extensionId}/view/";
            require($path . str_replace(".", "/", $view) . ".php");
            return ob_get_clean();
        }
    }
}
