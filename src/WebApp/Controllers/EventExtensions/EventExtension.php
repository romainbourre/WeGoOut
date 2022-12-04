<?php

namespace WebApp\Controllers\EventExtensions
{


    use System\Routing\Responses\Response;
    use WebApp\Controllers\AppController;

    abstract class EventExtension extends AppController
    {
        /**
         * EventExtension constructor.
         */
        public function __construct(public readonly string $extensionId)
        {
            parent::__construct();
            $this->autoloaderCSS();
            $this->autoloaderAjax();
            $this->autoloaderJS();
        }

        abstract public function computeActionQuery(string $action): Response;

        /**
         * Load the CSS file of the extension
         */
        public function autoloaderCSS(): void
        {
            $this->addCssStyle("css-{$this->extensionId}.css");
        }

        /**
         * Load the JS script of the extension
         */
        public function autoloaderJS(): void
        {
            $this->addJsScript("tab-{$this->extensionId}.js");
        }

        /**
         * Load the AJAX script of the extension
         */
        public function autoloaderAjax(): void
        {
            $this->addJsScript("ajax-{$this->extensionId}.js");
        }

        /**
         * Load extension's template
         * @param string $view view to load
         * @param array|null $variables data for view
         * @return string $content view
         */
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