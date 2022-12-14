<?php

namespace System\Controllers
{


    use Exception;
    use PhpLinq\PhpLinq;
    use System\Routing\Responses\BadRequestResponse;
    use System\Routing\Responses\InternalServerErrorResponse;
    use System\Routing\Responses\NotFoundResponse;
    use System\Routing\Responses\OkResponse;
    use System\Routing\Responses\UnauthorizedResponse;

    abstract class Controller implements ResponseControllerInterface
    {
        private static array $cssFiles = array();
        private static array $jsFiles = array();

        /**
         * @inheritDoc
         */
        public function ok(mixed $content = null): OkResponse
        {
            return new OkResponse($content);
        }

        /**
         * @inheritDoc
         */
        public function badRequest(mixed $content = null): BadRequestResponse
        {
            return new BadRequestResponse($content);
        }

        /**
         * @inheritDoc
         */
        public function internalServerError(mixed $content = null): InternalServerErrorResponse
        {
            return new InternalServerErrorResponse($content);
        }

        /**
         * @inheritDoc
         */
        public function notFound(mixed $content = null): NotFoundResponse
        {
            return new NotFoundResponse($content);
        }

        /**
         * @inheritDoc
         */
        public function unauthorized(mixed $content = null): UnauthorizedResponse
        {
            return new UnauthorizedResponse($content);
        }

        /**
         * @throws Exception
         */
        protected function render(string $view, array $variables = null): string
        {
            try {
                ob_start();
                if (!is_null($variables)) extract($variables);
                if (isset($css)) $css .= $this->getCssStyle();
                else $css = $this->getCssStyle();
                if (isset($js)) $js .= $this->getJsScript();
                else $js = $this->getJsScript();
                require_once(APP . "/Views/" . str_replace(".", "/", $view) . ".php");
                return ob_get_clean();
            } catch (Exception $e) {
                ob_clean();
                throw $e;
            }
        }

        protected function getCssStyle(): ?string
        {
            $cssFiles = PhpLinq::fromArray(self::$cssFiles);
            $css = $cssFiles
                ->select(fn(string $file) => "<link rel='stylesheet' href='/assets/css/$file'/>")
                ->toArray();
            return implode($css);
        }

        protected function addCssStyle(string $cssLink): ?string
        {
            self::$cssFiles[] = $cssLink;
            return self::getCssStyle();
        }

        protected function getJsScript(): string
        {
            $jsFiles = PhpLinq::fromArray(self::$jsFiles);
            $js = $jsFiles
                ->select(fn(string $file) => "<script type='module' src='/assets/js/$file'></script>")
                ->toArray();
            return implode($js);
        }

        protected function addJsScript(string $jsLink): string
        {
            self::$jsFiles[] = $jsLink;
            return self::getJsScript();
        }
    }
}
