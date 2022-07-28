<?php


namespace Domain\Interfaces;


use Domain\Exceptions\TemplateLoadingException;

interface ITemplateRenderer
{
    /**
     * Generate view from template with variables
     * @param string $template file of template
     * @param array $variables variable to push into template
     * @return string content of view
     * @throws TemplateLoadingException
     */
    public function render(string $template, array $variables): string;
}