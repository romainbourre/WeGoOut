<?php


namespace Adapters\TwigRenderer
{


    use Business\Exceptions\TemplateLoadingException;
    use Business\Ports\TemplateRendererInterface;
    use Twig\Environment;
    use Twig\Error\LoaderError;
    use Twig\Error\RuntimeError;
    use Twig\Error\SyntaxError;
    use Twig\Loader\FilesystemLoader;

    class TwigRendererAdapter implements TemplateRendererInterface
    {
        /**
         * @var Environment Twig instance
         */
        private Environment $twig;

        /**
         * TwigRendererAdapter constructor.
         * @param string $templatePath
         * @param bool $activeCache
         */
        public function __construct(string $templatePath, bool $activeCache = false)
        {
            $loader = new FilesystemLoader($templatePath);
            $options = [];

            if ($activeCache)
            {
                $options['cache'] = '/tmp/';
            }

            $this->twig = new Environment($loader, $options);
        }

        /**
         * @inheritDoc
         */
        public function render(string $template, array $variables): string
        {
            try
            {
                return $this->twig->render($template, $variables);
            }
            catch (LoaderError | RuntimeError | SyntaxError $e)
            {
                throw new TemplateLoadingException($e->getMessage());
            }
        }
    }
}