<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-platesrenderer for the canonical source repository
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-platesrenderer/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Plates\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class InvalidExtensionException extends RuntimeException implements
    ExceptionInterface,
    ContainerExceptionInterface
{
}
