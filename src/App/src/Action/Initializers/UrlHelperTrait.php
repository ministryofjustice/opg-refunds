<?php

namespace App\Action\Initializers;

use UnexpectedValueException;
use Zend\Expressive\Helper\UrlHelper;

/**
 * Getter and Setter, implementing the UrlHelperInterface.
 *
 * Class UrlHelperTrait
 * @package App\Action\Initializers
 */
trait UrlHelperTrait
{

    private $helper;

    public function setUrlHelper(UrlHelper $helper)
    {
        $this->helper = $helper;
    }

    public function getUrlHelper() : UrlHelper
    {

        if( !( $this->helper instanceof UrlHelper ) ){
            throw new UnexpectedValueException('UrlHelper not set');
        }

        return $this->helper;
    }

}
