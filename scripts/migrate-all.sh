#!/bin/bash

scripts/doctrine-migrations.sh Cases 'migrations:migrate --no-interaction -vvv'
scripts/doctrine-migrations.sh Sirius 'migrations:migrate --no-interaction -vvv'
