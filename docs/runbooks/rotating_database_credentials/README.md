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


### Example rotation procedure

``` bash
# Commands to replace a database credential

git clone https://github.com/ministryofjustice/opg-refunds.git
cd ~/environment/opg-refunds/
git checkout LPA-3561

. ~/environment/opg-refunds/docs/runbooks/cloud9/cloud9_init.sh preproduction

~/environment/opg-refunds/docs/runbooks/maintenance_mode/manage_maintenance.sh \
  --environment preproduction \
  --maintenance_mode

time ~/environment/opg-refunds/docs/runbooks/rotating_database_credentials/target_operations/password_update.sh \
  --environment preproduction \
  --database applications \
  --credential applications_write \
  --username applications


echo "Here we're doing some manual testing of refunds..."
sleep 20

~/environment/opg-refunds/docs/runbooks/maintenance_mode/manage_maintenance.sh \
  --environment preproduction \
  --disable_maintenance_mode
  ```
