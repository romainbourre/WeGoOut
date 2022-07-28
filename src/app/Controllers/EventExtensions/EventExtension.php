<?php

namespace App\Controllers\EventExtensions
{


    use App\Controllers\AppController;

    abstract class EventExtension extends AppController
    {
        private string $extensionId;

        /**
         * EventExtension constructor.
         */
        public function __construct(string $extensionId)
        {
            parent::__construct();
            $this->extensionId = $extensionId;
            $this->autoloaderCSS();
            $this->autoloaderAjax();
            $this->autoloaderJS();
        }

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
         * Get by include the ajax switch of the extension
         * @param string $action parameters for the ajax switch
         */
        public function getAjaxSwitch(string $action): void
        {
            include APP . "/Controllers/EventExtensions/Views/tab-{$this->extensionId}/ajax/switch.php";
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