#!/bin/bash

gosu app scripts/doctrine-migrations.sh Cases 'migrations:migrate --no-interaction -vvv'
gosu app scripts/doctrine-migrations.sh Sirius 'migrations:migrate --no-interaction -vvv'
