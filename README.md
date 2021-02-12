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
   docker run -v ${PWD}:/app itkdev/yarn:latest install
   ```

5. Run database migrations

   ```sh
   docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
   ```

6. Load database fixtures

   ```sh
   docker-compose exec phpfpm bin/console hautelook:fixtures:load --purge-with-truncate --no-interaction
   ```

You should now be able to browse to the application

```shell
open http://$(docker-compose port nginx 80)
```

## Running the tests

See the [TESTING.md](docs/TESTING.md) documentation for more information.

### Unit tests

```shell
docker-compose exec phpfpm bin/phpunit
```

### End-to-end tests

```sh
docker run -it -v $PWD:/e2e -w /e2e --network=host --env CYPRESS_baseUrl=http://$(docker-compose port nginx 80) cypress/included:6.4.0
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

* Markdown files (markdownlint standard rules)

  ```sh
  docker run -v ${PWD}:/app itkdev/yarn:latest markdownlint README.md
  ```

## Contributing

### Development Model

The [Git Flow branching model](https://nvie.com/posts/a-successful-git-branching-model/)
is used as our development model.

### Pull Request Process

Before opening a pull request, make sure that you have:

* Checked that your changes follows the coding standards described in the [README](README.md).
* Passed all the tests.
* Updated the [README](README.md) with details of changes to the interface,
  this includes new environment variables, useful file locations and container parameters.
* Updated the [CHANGELOG](CHANGELOG.md).

1. Create your feature branch (`git checkout -b feature/AmazingFeature`)
2. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
3. Push to the Branch (`git push origin feature/AmazingFeature`)
4. Open a Pull Request
5. You may merge your Pull Request when another team member has reviewed and
   approved your request.

### Coding Standards

The following coding standards are enforced in this project:

* PHP - [Symfony Coding Standards](https://symfony.com/doc/5.2/contributing/code/standards.html)
* Twig - [Twig Coding Standards](https://twig.symfony.com/doc/3.x/coding_standards.html)
* Javascript - [Javascript Standard Style](https://standardjs.com/rules.html)
* Markdown - [markdownlint rules](https://github.com/DavidAnson/markdownlint/blob/main/doc/Rules.md)

## Documentation

Documentation is placed in the [docs](docs) folder. Follow the guidelines described in the 
[DOCUMENTATION.md](docs/DOCUMENTATION.md) document whent writing documentation.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available,
see the [tags on this repository](https://github.com/itk-dev/naevnssekretariatet/tags).

## License

This project is licensed under the MIT License - see the
[LICENSE.md](LICENSE.md) file for details
