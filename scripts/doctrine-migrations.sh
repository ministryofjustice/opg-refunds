#!/bin/bash

echo "First parameter is database name e.g. Cases, Sirius, second parameter is doctrine-migrations command e.g. 'migrations:diff' or 'migrations:migrate --no-interaction -vvv'"

DATABASE=${1:-Cases}

cp DoctrineMigrations/${DATABASE}/cli-config.php .
cp DoctrineMigrations/${DATABASE}/migrations.yml .

vendor/bin/doctrine-migrations ${2}

rm cli-config.php
rm migrations.yml
