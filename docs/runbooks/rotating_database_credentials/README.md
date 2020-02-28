# Rotate Database Credentials

This script will generate a new random password, store it in secrets manager and update the specified database user with the new password as pulled from secrets manager.

## Setup

### Start a Cloud9 Instance

Set up and configure a Cloud9 instance using instructions in ../cloud9/README.md

### Get script and run it

Git clone the opg-refunds repository.

### Usage
Go to the rotating_database_credentials path and execute the script.

``` bash
cd ~/environment/opg-refunds/docs/runbooks/rotating_database_credentials
./target_operations/password_update.sh \
  --environment preproduction \
  --database applications \
  --credential applications_write \
  --username applications
```

You can perform a dry run with `--test true`
