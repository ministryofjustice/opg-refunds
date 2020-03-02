# Manage Maintenance Mode

This script will enable or disable maintenance mode for a targeted environment.

## Setup

### Start a Cloud9 Instance

Set up and configure a Cloud9 instance using instructions in ../cloud9/README.md

### Get script and run it

Git clone the opg-refunds repository.

### Dependencies

The install_terraform.sh script will get and checksum the specified version of terraform.

Go to https://www.terraform.io/downloads.html and retrieve the SHA256 checksum for the linux_amd64 version.

Provide the version and SHA256 checksum to the script, for example to install version 0.12.21 run

``` bash
cd ~/environment/opg-refunds/docs/runbooks/maintenance_mode
./install_terraform.sh \
  --version 0.12.21 \
  --sha256sum ca0d0796c79d14ee73a3d45649dab5e531f0768ee98da71b31e423e3278e9aa9
```

### Usage

Go to the rotating_database_credentials path and execute the script.

Set maintenance_mode to True to turn maintenance on

``` bash
cd ~/environment/opg-refunds/docs/runbooks/maintenance_mode
./manage_maintenance.sh \
  --environment preproduction \
  --maintenace_mode True
```

Set maintenance_mode to False to turn maintenance off

``` bash
cd ~/environment/opg-refunds/docs/runbooks/maintenance_mode
./manage_maintenance.sh \
  --environment preproduction \
  --maintenace_mode False
```
