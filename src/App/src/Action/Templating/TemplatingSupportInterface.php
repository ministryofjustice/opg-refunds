<?php

namespace App\Action\Templating;

use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Declares Action Middleware support template rendering.
 *
 * Interface TemplatingSupportInterface
 * @package App\Action\Templating
 */
interface TemplatingSupportInterface
{

    public function setTemplateRenderer(TemplateRendererInterface $template);

    public function getTemplateRenderer() : TemplateRendererInterface;

}
