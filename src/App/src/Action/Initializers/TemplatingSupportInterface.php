<?php

namespace App\Action\Initializers;

use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Declares Action Middleware support template rendering.
 *
 * Interface TemplatingSupportInterface
 * @package App\Action\Initializers
 */
interface TemplatingSupportInterface
{

    public function setTemplateRenderer(TemplateRendererInterface $template);

    public function getTemplateRenderer() : TemplateRendererInterface;

}
