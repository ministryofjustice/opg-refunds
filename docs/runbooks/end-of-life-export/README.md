# Export Tool

This tool exports claims & payments via a cloud9 instance in the environment you need.

## Setup

**Please ensure you run the [cloud9_init.sh]('../cloud9/README.md) here first** to ensure you can connect to all parts of the environment.

Now please copy the `./export.py` script and `./requirements.txt` into your cloud9 instance.

Add execute permissions to the python script:

```bash
chmod +x ./export.py
```

Install pip dependancies:

```bash
pip install --user -r requirements.txt
```


This script requires you to pass in the connection details for postgres; the script uses the `cases` database and requires login creds for that.

To get the password from secret manager run this:

```bash
export CASES_DB_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${ACCOUNT}/opg_refunds_db_cases_full_password | jq -r .'SecretString')
export CASES_DB_USER='cases_full'
export CASES_DB='cases'
```


To generate the spreadsheet now run this command:

```bash
python ./export.py --dbHost=${CASEWORKER_HOST} --dbPort='5432' --dbUser="${CASES_DB_USER}" --dbPwd="${CASES_DB_PASSWORD}" --db=${CASES_DB}
```

This will create file at `./Export.xlsx`


You can connect to the DB using:

```bash
psql -h ${CASEWORKER_HOST} ${CASES_DB} -W -U ${CASES_DB_USER}
```
