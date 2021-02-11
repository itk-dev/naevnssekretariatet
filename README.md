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

## Contributing
### Development Model
The [Git Flow branching model](https://nvie.com/posts/a-successful-git-branching-model/) is used as our development model.

### Pull Request Process
1. Create your feature branch (`git checkout -b feature/AmazingFeature`)
2. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
3. Push to the Branch (`git push origin feature/AmazingFeature`)
4. Open a Pull Request
5. You may merge your Pull Request when another team member has reviewed and approved your request.

## License
This project is licensed under the MIT License - see the
[LICENSE.md](LICENSE.md) file for details