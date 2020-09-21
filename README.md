# LPA Refunds Service
The Office of the Public Guardian LPA Refunds service: Managed by opg-org-infra &amp; Terraform.


## Local Development Setup

Intially, download the repo via:

```
git clone git@github.com:ministryofjustice/opg-refunds.git
cd opg-refunds
```

Within `opg-refunds` directory to *run* the project for the first time use the following:

```
make dc-run
make
```

The `Makefile` will fetch secrets using `aws secretsmanager` and `docker-compose` commands together to pass along environment variables removing the need for local configuration files.


The Public service will be available via https://localhost:9001/start
The Caseworker service will be available via https://localhost:9002

The Caseworker API service will be available (direct) via http://localhost:9003

After the first time, you can *run* the project by:
```
make
```

### Updating composer dependencies

Composer install is run when the app containers are built, and on a standard `docker-compose up`.

It can also be run independently with:
```bash
docker-compose run <service>-composer
```

New packages can be added with:
```bash
docker-compose run <service>-composer composer require author/package
```

Packages can be removed with:
```bash
docker-compose run <service>-composer composer remove author/package
```
