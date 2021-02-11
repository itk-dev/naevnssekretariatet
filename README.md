# Naevnssekretariatet
Application for managing multiple boards (Danish: Nævn) in multiple municipalities.

## About the project
A danish municipality has different boards (Danish: Nævn) which handles different processes.
For example a process in the board for rent (Danish: Huslejenævnet) handles the process where tenants make complaints
about their rent.

This application lets stakeholders in a process submit information the members of a board can make a decision upon.

### Built with
* [Symfony](https://symfony.com)

## Getting started
To get a local copy up and running follow these simple steps.

### Prerequisites
- [Docker](https://docs.docker.com/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

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

You should now be able to browse to the application
```shell
open http://$(docker-compose port nginx 80)
```