<?php

namespace App\Action\Initializers;

use Mezzio\Helper\UrlHelper;

/**
 * Declares Action Middleware support for UrlHelper
 *
 * Interface UrlHelperInterface
 * @package App\Action\Initializers
 */
interface UrlHelperInterface
{

    public function setUrlHelper(UrlHelper $template);

    public function getUrlHelper() : UrlHelper;
}
