# opg-refunds-logger
Common logging component for OPG Refunds

## Usage

### Direct

`Opg\Refunds\Log\Logger` extends `Zend\Log\Logger`. 
This module provides an instance of the Refunds Logger via:
```php
$container->get(Zend\Log\Logger::class)
```

### Via Initializer

You can have the Logger injected into any instance created via the service manager
using the Initializer.

The Initializer will inject an instance of `Opg\Refunds\Log\Logger` into any instance
that has the interface: `Opg\Refunds\Log\Initialize\LogSupportInterface`.

The trait `Opg\Refunds\Log\Initialize\LogSupportTrait` provides an implementation
for the above interface.

You can then access the Logger via `$this->getLogger()`.

For example
```php
<?php

use Opg\Refunds\Log\Initializer;

class Example implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;
    
    public function test()
    {
        $this->getLogger()->info('Example log message');
    }
}
```
