<?php

namespace App\Action\Initializers;

use UnexpectedValueException;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Getter and Setter, implementing the TemplatingSupportInterface.
 *
 * Class TemplatingSupportTrait
 * @package App\Action\Initializers
 */
trait TemplatingSupportTrait
{

    private $template;

    public function setTemplateRenderer(TemplateRendererInterface $template)
    {
        $this->template = $template;
    }

    public function getTemplateRenderer() : TemplateRendererInterface
    {

        if (!( $this->template instanceof TemplateRendererInterface )) {
            throw new UnexpectedValueException('TemplateRenderer not set');
        }

        return $this->template;
    }
}
