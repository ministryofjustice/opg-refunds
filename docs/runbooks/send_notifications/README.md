# Extraction and run of bulk notifications data

## Setup

### Start a Cloud9 Instance

Set up and configure a Cloud9 instance using instructions in ../cloud9/README.md

### Get script and run it

Git clone the opg-refunds repository.

``` sh
cd ~/environment
git clone https://github.com.ministryofjustice/opg-refunds
```

make sure you are in the right directory for the scripts

```sh
cd ~/environment/opg-refunds/docs/runbooks/send-notifications/
```

## Extract data

### login

log in to PSQL on the cloud 9 instance. to do this you will need the password for the cases_full user, which can be retrieved from secrets manager. then set up this as your PGPASSWORD and login:

```sh
cd ~/environments/opg-refunds
export PGPASSWORD=<environment cases_full_pass>
psql -h $CASEWORKER_HOST -U cases_full -d cases
```

you should be at the PSQL prompt.

```postgresql
cases=>
```

### run sql script

at the prompt run the following:

```postgresql
cases=> \i current_claim_contacts.sql
```

this will produce a file called `contact_list.csv`
once done enter `\q` to quit.

sanity check the contents of the file produced by opening in cloud9.

### install python requirements

run the install script:

``` sh
pip install -r requirements.txt --user`
```

### run python script

in order to run this script you will need:

notify api key - stored in secrets manager for your environment
sms notify template id
email notify template id
file name produced by sql script

then run the following:

```sh
python send-notifications.py \
    --notify_api_key <notify_api_key> \
    --filename <filename.csv> \
    --sms_template_id <sms_template_id> \
    --email_template_id <email_template_id>
```

this will print responses back from the notify API for each of the requests.
