<?php

return (getenv('OPG_REFUNDS_STACK_TYPE') !== 'loadtest') ? [] :
[

    'beta' => [
        'enabled' => false,
    ],

];
