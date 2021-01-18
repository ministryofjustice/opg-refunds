# Manage Maintenance Mode and Shutdown mode

This script will enable or disable the following for a targeted environment:

- Maintenance Mode: fixed response telling users the service is under maintenance
- Shutdown Mode: permanent redirect back to .gov.uk landing page. ***Use with care!***

## Set up on AWS console

You will need access to the relevant aws account via the AWS console to do these instructions.

### Start a Cloud9 Instance

Set up and configure a Cloud9 instance using instructions in ../cloud9/README.md

### Get script and go to it's location

Git clone the opg-refunds repository, and go to the directory where the script is:

```bash
cd ~/environment/opg-refunds/docs/runbooks/maintenance_mode
```

## Setup to run locally

This assumes you have to aws profiles, aws-vault and the AWS CLI installed on your machine. Please contact a webops engineer who can assist if you haven't got access to these.

For the main running scripts to work below, you will need to prefix each command with the appropriate `aws-vault` profile call. E.g. for preproduction, and assuming that you have the right access:

```bash
aws-vault exec refunds-preprod-breakglass -- <command>
```

Contact a Webops engineer if you do not have the access required

## Running Scripts

### Maintenance Mode

Set maintenance_mode to True to turn maintenance on

``` bash

./manage_maintenance.sh \
  --environment preproduction \
  --maintenance_mode
```

Set maintenance_mode to False to turn maintenance off

``` bash
./manage_maintenance.sh \
  --environment preproduction \
  --disable_maintenance_mode
```

### Shutdown Mode

**This is for decommissioning the Service, so use only when ready.** Running this will result in a permanent redirect being sent to browsers and potentially caching the .gov page as the result, which means users would need to clear browser caches if we wanted to reverse this.

Set shutdown_mode to True to turn  on redirect to .gov page.

``` bash
./manage_maintenance.sh \
  --environment preproduction \
  --shutdown_enabled
```

Set shutdown_mode to False to turn redirect off

``` bash
./manage_maintenance.sh \
  --environment preproduction \
  --shutdown_disabled
```
