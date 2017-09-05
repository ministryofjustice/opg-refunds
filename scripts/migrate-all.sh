#!/bin/bash

scripts/doctrine-migrations.sh Auth 'migrations:migrate --no-interaction -vvv'
scripts/doctrine-migrations.sh Cases 'migrations:migrate --no-interaction -vvv'
