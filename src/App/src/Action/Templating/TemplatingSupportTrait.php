<?php

namespace App\Action\Templating;

use UnexpectedValueException;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Getter and Setter, implementing the TemplatingSupportInterface.
 *
 * Class TemplatingSupportTrait
 * @package App\Action\Templating
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

        if( !( $this->template instanceof TemplateRendererInterface ) ){
            throw new UnexpectedValueException('TemplateRenderer not set');
        }

        return $this->template;
    }

}
