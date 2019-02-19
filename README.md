# opg-lpa-refund-docker

Initial boilerplate setup for docker-refund project.  This will create an application stack with the following components

- public frontend container
- caseworker api container
- caseworker frontend container
- nginx router
- postgres 9.6.2 container
- dynamodb local container
- Key management service local container
- Simple notification service local container

### Access URLs

**Public front:** https://refunds-public-front.local/

**Caseworker front:** https://refunds-caseworker-front.local/

**Caseworker API:** https://refunds-caseworker-api.local/


### Quick Start

Make sure that port 443 is not already in use on your local machine.

**If this is the first time you are building the software**

Login to the docker registry, if you haven't already (Get the username and password from your team-lead or ops)
```
docker login https://registry.service.opg.digital
```
Set up a virtual env, install the python packages, add alias' to the host file, and bring up the containers
```
sudo pip install virtualenv` 
virtualenv venv`
. venv/bin/activate`
pip install -U -r requirements.txt`
echo "127.0.0.1 refunds-public-front.local refunds-caseworker-front.local refunds-caseworker-api.local" | sudo tee -a /etc/hosts`
docker-compose up` 
```
Now run the [DynamoDB setup script](#DynamoDB) and [Development Environment Database Setup](#Development-Environment-Database-Setup)

**If you have built the software before**
```
. venv/bin/activate`
docker-compose build`
docker-compose up`
```
**To deactivate the virtualenv once done**
``` 
deactivate`
```
## DynamoDB
The docker-compose script starts a local dynamodb container based on the Amazon Local DynamoDB software (http://docs.aws.amazon.com/amazondynamodb/latest/developerguide/DynamoDBLocal.UsageNotes.html).

Using the virtualenv created above the local dynamodb schema can be create using the **scripts/dynamodb-init.sh** script.
```
. venv/bin/activate`
. scripts/dynamodb-init.sh`
```

## Postgres
The postgres container will automatically setup five databases and related users for each database. They are as follows:
 
- applications: (See `init-db-applications.sql` file for details) Database for the public facing web site. Contains a single application table where refund applictaions are stored. Users:
    - Write only user with **INSERT** only permissions on the application table only. Used by the public web site
    - API user with **SELECT, INSERT, UPDATE, DELETE** on all tables. Used by the caseworker API
    - Migration user with **ALL PRIVILEGES** on all tables, schemas and the database itself. Used for creating and running doctrine migrations
- cases: (See `init-db-cases.sql` file for details) Database for the caseworker web site. Users:
    - API user with **SELECT, INSERT, UPDATE, DELETE** on all tables
    - Migration user with **ALL PRIVILEGES** on all tables, schemas and the database itself. Used for creating and running doctrine migrations
- sirius: (See `init-db-sirius.sql` file for details) Reference database for the caseworker web site. Users:
    - API user with **SELECT, INSERT, UPDATE, DELETE** on all tables
    - Migration user with **ALL PRIVILEGES** on all tables, schemas and the database itself. Used for creating and running doctrine migrations
- meris: (See `init-db-meris.sql` file for details) Reference database for the caseworker web site. Users:
    - API user with **SELECT, INSERT, UPDATE, DELETE** on all tables
    - Migration user with **ALL PRIVILEGES** on all tables, schemas and the database itself. Used for creating and running doctrine migrations
- finance: (See `init-db-finance.sql` file for details) Reference database for the caseworker web site. Users:
    - API user with **SELECT, INSERT, UPDATE, DELETE** on all tables
    - Migration user with **ALL PRIVILEGES** on all tables, schemas and the database itself. Used for creating and running doctrine migrations
 
### Development Environment Database Setup

- Ensure that your local postgres service has been recreated **if it was created before 11/09/2017** by running `docker-compose rm -f postgres`
- run `docker-compose run --rm api bash -c "cd app && scripts/migrate-all.sh"`

#### Updating migration scripts

- Update the Entities in opg-refunds-caseworker-api with the required mappings
- Run `docker-compose run --rm api gosu app bash -c "cd app && scripts/doctrine-migrations.sh Cases migrations:diff"`

### Troubleshooting

If the application fails to connect to dynamodb after following all the steps. The commands below may help
```
docker-compose down #Stops running containers and removes them
docker rm $(docker ps -qa) # Removes any old containers
docker volumes prune # This will kill any persisted data if you get session errors or db schema errors this may be the solution
docker rmi -f $(docker images | grep refunds | tr -s ' ' | cut -d ' ' -f3 | sort | uniq) # Will remove all the images with refunds in the tag
```
