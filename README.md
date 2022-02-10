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

   ```shell
   git clone git@github.com:itk-dev/naevnssekretariatet.git
   ```

2. Pull docker images and start docker containers

   ```shell
   docker-compose pull
   docker-compose up --detach
   ```

3. Install composer packages

   ```shell
   docker-compose exec phpfpm composer install
   ```

4. Install yarn packages

   ```sh
   docker run -v ${PWD}:/app node:16 yarn --cwd=/app install
   ```

5. Build assets

   ```sh
   docker run -v ${PWD}:/app node:16 yarn --cwd=/app build
   ```

   During development, run

   ```sh
   docker run -v ${PWD}:/app node:16 yarn --cwd=/app dev
   ```

   and to watch for for changes run

   ```sh
   docker run --interactive --tty -v ${PWD}:/app node:16 yarn --cwd=/app dev --watch
   ```

6. Run database migrations

   ```sh
   docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
   ```

7. Load database fixtures

   ```sh
   docker-compose exec phpfpm bin/console hautelook:fixtures:load --no-bundles --purge-with-truncate --no-interaction
   ```

You should now be able to browse to the application

```shell
open http://$(docker-compose port nginx 80)
```

#### Azure B2C

Configuration of the following environment variables
must be done in order to login via Azure B2C:

```shell
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

```shell
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

```shell
DEFAULT_URI=
```

See [Symfony documentation](https://symfon.com/doc/current/routing.html#generating-urls-in-commands)
for more information.

## Sign in from command line

Rather than signing in via Azure B2C, you can get
a sign in url from the command line. Run

```shell
bin/console itk-dev:openid-connect:login --help
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

## Running the tests

See the [TESTING.md](docs/TESTING.md) documentation for more information.

### Unit tests

```shell
docker-compose exec phpfpm bin/phpunit
```

### End-to-end tests

```sh
docker run -it -v $PWD:/e2e -w /e2e --network=host \
--env CYPRESS_baseUrl=http://$(docker-compose port nginx 80) cypress/included:6.5.0
```

### Coding standard tests

The following commands let you test that the code follows the coding standards
we decided to adhere to in this project.

* PHP files (PHP-CS-Fixer with the Symfony ruleset enabled)

   ```shell
   docker-compose exec phpfpm vendor/bin/php-cs-fixer fix --dry-run
   ```

* Twig templates (Twigcs with standard settings)

   ```shell
   docker-compose exec phpfpm vendor/bin/twigcs templates
   ```

* Javascript files (Standard with standard settings)

  ```sh
  docker run -v ${PWD}:/app itkdev/yarn:latest standard
  ```

* Sass files (Sass guidelines)

  ```sh
  docker run -v ${PWD}:/app itkdev/yarn:latest stylelint "assets/**/*.scss"
  ```

* Markdown files (markdownlint standard rules)

  ```sh
  docker run -v ${PWD}:/app itkdev/yarn:latest markdownlint README.md
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
