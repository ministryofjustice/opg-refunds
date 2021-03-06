#!/bin/sh

cd /app

seedData="${OPG_LPA_SEED_DATA:-false}"

COUNTER=0

while : ; do

    bin/lock acquire --table $OPG_LPA_COMMON_CRONLOCK_DYNAMODB_TABLE \
    --name "$OPG_LPA_STACK_NAME/db-migrate" --ttl 60 \
    --endpoint $OPG_LPA_COMMON_DYNAMODB_ENDPOINT

    retval=$?

    if [ $retval -eq 0 ]; then
        # Acquired lock
        vendor/robmorgan/phinx/bin/phinx migrate
        if ${seedData}; then
            vendor/robmorgan/phinx/bin/phinx seed:run
        fi
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
