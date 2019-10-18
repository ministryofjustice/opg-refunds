# LPA Refunds Service
The Office of the Public Guardian LPA Refunds service: Managed by opg-org-infra &amp; Terraform.


## Local Development Setup

The first time you bring up the environment:

```
git clone git@github.com:ministryofjustice/opg-refunds.git
cd opg-refunds

docker-compose run public-composer
docker-compose run caseworker-front-composer
docker-compose run caseworker-api-composer

docker-compose up
```

You will also need a copy of the local config file `public-front/config/autoload/local.php`. Any developer on the team
should be able to provide you with this.


The Public service will be available via https://localhost:9001/start
The Caseworker service will be available via https://localhost:9002

The Caseworker API service will be available (direct) via http://localhost:9003

After the first time, you bring up the environment with:
```
docker-compose up
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
