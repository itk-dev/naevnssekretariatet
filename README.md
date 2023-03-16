# Naevnssekretariatet

Application for managing multiple boards (Danish: Nævn) in multiple municipalities.

## About the project

A danish municipality has different boards (Danish: Nævn) which handles
different processes.
For example a process in the board for rent (Danish: Huslejenævnet) handles
the process where tenants make complaints about their rent.

This application lets stakeholders in a process submit information the members
of a board can make a decision upon.

### Built with

* [Symfony](https://symfony.com)
* [Encore](https://symfony.com/doc/current/frontend.html)
* [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle)

## Getting started

To get a local copy up and running follow these simple steps.

### Prerequisites

* [Docker](https://docs.docker.com/install/)
* [Docker Compose](https://docs.docker.com/compose/install/)

### Installation

1. Clone the repo

   ```sh
   git clone git@github.com:itk-dev/naevnssekretariatet.git
   ```

2. Pull docker images and start docker containers

   ```sh
   docker compose pull
   docker compose up --detach --build
   ```

3. Install composer packages

   ```sh
   docker compose exec phpfpm composer install
   ```

4. Install yarn packages

   ```sh
   docker compose run --rm node yarn install
   ```

5. Build assets

   ```sh
   docker compose run --rm node yarn build
   ```

   During development, run

   ```sh
   docker compose run --rm node yarn dev
   ```

   and to watch for for changes run

   ```sh
   docker compose run --rm node yarn watch
   ```

6. Run database migrations

   ```sh
   docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
   ```

7. Load database fixtures

   ```sh
   docker compose exec phpfpm bin/console hautelook:fixtures:load --no-bundles --purge-with-truncate --no-interaction
   ```

You should now be able to browse to the application

```sh
open "http://$(docker compose port nginx 8080)"
```

Sign in as `admin@example.com`:

```sh
open "$(docker compose exec --env DEFAULT_URI="http://$(docker compose port nginx 8080)" phpfpm bin/console itk-dev:openid-connect:login admin@example.com)"
```

#### Azure B2C

Configuration of the following environment variables
must be done in order to login via Azure B2C:

```sh
###> itk-dev/openid-connect-bundle ###
CONFIGURATION_URL=APP_CONFIGURATION_URL
CLIENT_ID=APP_CLIENT_ID
CLIENT_SECRET=APP_CLIENT_SECRET
CALLBACK_URI=APP_CALLBACK_URI
CLI_REDIRECT=APP_CLI_REDIRECT_URI
LEEWAY=APP_LEEWAY
###< itk-dev/openid-connect-bundle ###
```

Example configuration:

```sh
CONFIGURATION_URL='https://.../.well-known/openid-configuration...'
CLIENT_ID={app.client.id}
CLIENT_SECRET={app.client.secret}
CALLBACK_URI={app.callback.uri}
CLI_REDIRECT={app.cli.redirect}
LEEWAY=10
```

#### CLI login

In order to use the CLI login feature the following
environment variable must be set:

```sh
DEFAULT_URI=
```

See [Symfony documentation](https://symfon.com/doc/current/routing.html#generating-urls-in-commands)
for more information.

## Sign in from command line

Rather than signing in via Azure B2C, you can get
a sign in url from the command line. Run

```sh
docker compose exec phpfpm bin/console itk-dev:openid-connect:login --help
```

for details. Be aware that a login url will only work once.

## Authentication providers

A user can sign in as “administrator” or “board member”, and on the login page,
`/login`, buttons for choosing authentication methods are shown.

### Request authentication type in url

A specific authentication provider can be requested via the `role` query
parameter in a url, i.e. if the url
`/case/7e62b7b8-2083-415c-8ddb-0ad9b5ed3d27/communication?role=board-member` is
accessed then the user must authenticate as a board member (if not already
authenticated).

The following roles and, hence, authentication providers can be requested:

| role         | authentication provider |
|--------------|-------------------------|
| admin        | administrator           |
| board-member | board member            |

## CPR and CVR lookup service

The following environment variables must be set in the `.env.local` file:

```sh
# Azure
AZURE_TENANT_ID='xyz'
AZURE_APPLICATION_ID='xyz'
AZURE_CLIENT_SECRET='xyz'

# CPR lookup
AZURE_KEY_VAULT_CPR_NAME='xyz'
AZURE_KEY_VAULT_CPR_SECRET='xyz'
AZURE_KEY_VAULT_CPR_SECRET_VERSION='xyz'

SERVICEPLATFORMEN_CPR_SERVICE_AGREEMENT_UUID='xyz'
SERVICEPLATFORMEN_CPR_USER_SYSTEM_UUID='xyz'
SERVICEPLATFORMEN_CPR_USER_UUID='xyz'

SERVICEPLATFORMEN_CPR_SERVICE_UUID='xyz'
SERVICEPLATFORMEN_CPR_SERVICE_ENDPOINT='https://xyz.com'
SERVICEPLATFORMEN_CPR_SERVICE_CONTRACT='%kernel.project_dir%/vendor/itk-dev/serviceplatformen/resources/person-base-data-extended-service-contract/wsdl/context/PersonBaseDataExtendedService.wsdl'

# CVR lookup
AZURE_KEY_VAULT_DATAFORDELER_NAME='xyz'
AZURE_KEY_VAULT_DATAFORDELER_SECRET='xyz'
AZURE_KEY_VAULT_DATAFORDELER_SECRET_VERSION='xyz'

DATAFORDELER_CVR_LOOKUP_BASE_URL='https://xyz.com'
```

## OS2Forms

We use OS2Forms (selvbetjening) for allowing citizen creating cases and hearing responses.

### Sending the submission to TVIST1

To send a submission from a OS2Forms webform to TVIST1 the webform should
contain an API request handler.
This should be configured with an API url and an authorization header.

| Configuration            | Value                                    |
|--------------------------|------------------------------------------|
| API url                  | `https://[site]/api/os2forms/submission` |
| API authorization header | `xyz`                                    |

The API authorization header should be set in
`.env.local` as `TVIST1_API_TOKEN`.

### Handling the submission in TVIST1

To handle submissions in TVIST1, the respective webform ids must be added
to the `handleOS2FormsSubmission` method in the `OS2FormsManager` class.
Here they should be forwarded to be processed.

## Cron job

In TVIST1 there are several necessary commands used for updating statuses,
sending digital post etc. These can conveniently be run via cron jobs.

With cron you could run a specific console command every night at 02:00
by adding the following to your crontab:

```cron
0 2 * * * /usr/bin/env php path/to/tvist1/bin/console tvist1:some:command
```

Or if using docker

```cron
0 2 * * * (cd path/to/tvist1/ && docker compose --env-file .env.docker.local --file docker-compose.server.yml exec phpfpm bin/console tvist1:some:command) > /dev/null 2>&1; /usr/local/bin/cron-exit-status -c 'TVIST1 some command' -v $?
```

where

```cron
> /dev/null 2>&1; /usr/local/bin/cron-exit-status -c 'TVIST1 some command' -v $?
```

Ensures that nothing is output in the terminal and helps debugging at a later stage.

The commands that need execution are

### Updating reminders

```cron
0 2 * * * /usr/bin/env php path/to/tvist1/bin/console tvist1:update-reminder
```

Updates reminder statuses at 02:00.

### Updating case deadlines

```cron
0 2 * * * /usr/bin/env php path/to/tvist1/bin/console tvist1:update-case-deadlines
```

Updates case deadline statuses at at 02:00.

### Send digital post

See [Digital post queue](docs/NgDP.md#digital-post-queue) for details on how to
handle sending digital post.

## Release process

The release process is very similar for staging and production,
with only slight deviation.

**Notice**, to help the release process,
a deploy script has been placed in the `scripts` folder
on both the staging and production server.
The script takes one argument, namely a branch name or git tag,
and ensure that all the necessary commands below are executed.

### Release commands

Make sure you are in the correct directory (`.../htdocs`)
then checkout branch or release tag:

```sh
git fetch
git checkout --force {some_branch_or_tag}
git reset origin/{some_branch_or_tag} --hard
git pull
```

And continue the process with the following commands.

```sh
# Create, recreate, build and/or start containers
docker compose --env-file .env.docker.local --file docker-compose.server.yml up --detach --build --remove-orphans
# Restart container to reload configuration (cf. https://docs.nginx.com/nginx/admin-guide/installing-nginx/installing-nginx-docker/#controlling-nginx)
docker compose --env-file .env.docker.local --file docker-compose.server.yml restart nginx
# @see https://stackoverflow.com/questions/36107400/composer-update-memory-limit
docker compose --env-file .env.docker.local --file docker-compose.server.yml exec --env COMPOSER_MEMORY_LIMIT=-1 --user deploy phpfpm composer install

# Build assets

docker compose run --rm node yarn install
docker compose run --rm node yarn build

docker compose --env-file .env.docker.local --file docker-compose.server.yml exec --user deploy phpfpm bin/console cache:clear
docker compose --env-file .env.docker.local --file docker-compose.server.yml exec --user deploy phpfpm bin/console assets:install public
docker compose --env-file .env.docker.local --file docker-compose.server.yml exec --user deploy phpfpm bin/console doctrine:migrations:migrate --no-interaction

###> PRODUCTION ONLY ###
docker compose --env-file .env.docker.local --file docker-compose.server.yml exec --user deploy phpfpm composer dump-env prod
###< PRODUCTION ONLY ###

###> STAGING ONLY ###
# Staging using fixtures, which means data is 'reset' upon release.
docker compose --env-file .env.docker.local --file docker-compose.server.yml exec --user deploy phpfpm bin/console hautelook:fixtures:load --purge-with-truncate --no-interaction
###< STAGING ONLY ###
```

## Running the tests

See the [TESTING.md](docs/TESTING.md) documentation for more information.

### Unit tests

```sh
docker compose exec phpfpm bin/phpunit
```

### End-to-end tests

```sh
docker run -it -v $PWD:/e2e -w /e2e --network=host \
--env CYPRESS_baseUrl=http://$(docker compose port nginx 8080) cypress/included:6.5.0
```

### Coding standard tests

The following commands let you test that the code follows the coding standards
we decided to adhere to in this project.

* PHP files (PHP-CS-Fixer with the Symfony ruleset enabled)

   ```sh
   docker compose exec phpfpm vendor/bin/php-cs-fixer fix --dry-run
   ```

* Twig templates (Twigcs with standard settings)

   ```sh
   docker compose exec phpfpm vendor/bin/twigcs templates
   ```

* Javascript files (Standard with standard settings)

  ```sh
  docker compose run --rm node yarn check-coding-standards/standard
  ```

* Sass files (Sass guidelines)

  ```sh
  docker compose run --rm node yarn check-coding-standards/stylelint
  ```

* Markdown files (markdownlint standard rules)

  ```sh
  docker compose run --rm node yarn check-coding-standards/markdownlint
  ```

## Code analysis

We use [PHPStan](https://phpstan.org/) and [Psalm](https://psalm.dev/) for code
analysis.

To analyse the code with both tools, run

```sh
composer code-analysis
```

Alternatively you can run just a single tool with

```sh
composer code-analysis/phpstan
```

or

```sh
composer code-analysis/psalm
```

## Contributing

See the [CONTRIBUTING.md](docs/CONTRIBUTING.md) documentation file.

## Documentation

Documentation is placed in the [docs](docs) folder.
Follow the guidelines described in the [DOCUMENTATION.md](docs/DOCUMENTATION.md)
document when writing documentation.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available,
see the [tags on this repository](https://github.com/itk-dev/naevnssekretariatet/tags).

## License

This project is licensed under the MIT License - see the
[LICENSE.md](LICENSE.md) file for details
