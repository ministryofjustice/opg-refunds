#!/bin/sh

/usr/local/bin/waitforit -address=tcp://${OPG_REFUNDS_DB_CASES_HOSTNAME}:${OPG_REFUNDS_DB_CASES_PORT} -timeout 60 -retry 6000 -debug
/usr/local/bin/waitforit -address=tcp://${OPG_REFUNDS_DB_SIRIUS_HOSTNAME}:${OPG_REFUNDS_DB_SIRIUS_PORT} -timeout 60 -retry 6000 -debug
/usr/local/bin/waitforit -address=tcp://${OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME}:${OPG_REFUNDS_DB_APPLICATIONS_PORT} -timeout 60 -retry 6000 -debug

cd /app

COUNTER=0

while : ; do

    bin/lock acquire --table $OPG_REFUNDS_CRONLOCK_DYNAMODB_TABLE \
    --name "db-migrate" --ttl 60 \
    --endpoint $OPG_REFUNDS_DYNAMODB_ENDPOINT

    retval=$?

    if [ $retval -eq 0 ]; then
        # Acquired lock

        psql -h ${OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME} -p ${OPG_REFUNDS_DB_APPLICATIONS_PORT} -U ${POSTGRES_USER} -f /app/scripts/init-db-applications.sql
        psql -h ${OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME} -p ${OPG_REFUNDS_DB_APPLICATIONS_PORT} -U ${POSTGRES_USER} -f /app/scripts/init-db-cases.sql
        psql -h ${OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME} -p ${OPG_REFUNDS_DB_APPLICATIONS_PORT} -U ${POSTGRES_USER} -f /app/scripts/init-db-finance.sql
        psql -h ${OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME} -p ${OPG_REFUNDS_DB_APPLICATIONS_PORT} -U ${POSTGRES_USER} -f /app/scripts/init-db-meris.sql
        psql -h ${OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME} -p ${OPG_REFUNDS_DB_APPLICATIONS_PORT} -U ${POSTGRES_USER} -f /app/scripts/init-db-sirius.sql

        /app/scripts/doctrine-migrations.sh Cases 'migrations:migrate --no-interaction -vvv'
        /app/scripts/doctrine-migrations.sh Sirius 'migrations:migrate --no-interaction -vvv'
        break
    elif [ $retval -eq 1 ]; then
        # Lock not acquired
        break
    else
        let COUNTER=COUNTER+1

        if [ $COUNTER -gt 10 ]; then
            echo "Fatal error: Unable to attempt migrations"
            exit 1
        fi

        echo "Error with lock system; will re-try"
        sleep 2
    fi

done

exit 0
