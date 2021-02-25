# Testing

This document introduces the types of tests we utilize, which tools we use
for testing, and lastly describes how to structure and write tests.

In this project we make use of both unit tests and end-to-end tests. We've
decided that the major focus should be on end-to-end tests, and only write
unit tests when it makes sense.

## Unit tests

The purpose of unit tests is to make sure that a section of an application
meets its design and behaves as intended.

To make these tests we use the [PHPUnit](https://phpunit.de/) testing framework.

### When to unit test

A lot of times it's not clear if a unit test brings value,
or just adds boilerplate code.
There are of course instances where a unit test makes perfectly sense.
Here are some pointers to decide:

* Functions/methods that makes calculations.
* Making sure that a bug is solved.

### How to write and organize unit tests

This project follows the testing [guidelines](https://symfony.com/doc/current/testing.html)
described within the Symfony framework.

### How to run unit tests

Run the following command in a terminal:

```shell
docker-compose exec phpfpm bin/phpunit
```

### Useful links

* [PHPUnit](https://phpunit.de/)
* [Symfony guidelines for writing and organizing tests](https://symfony.com/doc/current/testing.html)

## End-to-end tests

The main purpose of End-to-end (E2E) testing is to test from the end user's
experience by simulating the real user scenario and validating the system
under test and its components for integration and data integrity.

To enable us making these tests, we use version 6.4.0 of the
[Cypress](https://www.cypress.io/) library.

### When to E2E test

In a perfect scenario every feature should have a corresponding E2E test.
This should be the rule of thumb, but it may not always be obvious if that
rule should be followed. In such cases the feature developer should reach
out to other team members and discuss the path to follow.

### How to write and organize E2E tests

This project follows the [guidelines](https://docs.cypress.io/guides/core-concepts/writing-and-organizing-tests.html)
described in the Cypress documentation for writing and organizing tests.

### How ro run E2E tests

Run the following command in a terminal:

```sh
docker run -it -v $PWD:/e2e -w /e2e --network=host \
--env CYPRESS_baseUrl=http://$(docker-compose port nginx 80) cypress/included:6.5.0
```

### Information about coding standards

The [JavaScript Standard Style](https://standardjs.com/rules.html) is used as coding
standards for all of this projects JavaScript files, and as the E2E tests are written
in JavaScript the coding standards should be followed.

One of the rules in the [JavaScript Standard Style](https://standardjs.com/rules.html)
requires that functions should not be placed in the global namespace, but Cypress
places several functions in the global namespace. To let the E2E tests adhere
to the coding standards, the global namespace functions should be ignored.
Luckily the [JavaScript Standard tool](https://standardjs.com/) we use for checking
the code for adhering to the coding standard allows us to do that.

Place the following in top of all E2E tests:

```javascript
/* global describe it expect */
```

Where all words after global are names of functions Cypress places in the
global namespace.
Over time as tests are developed it may be necessary to expand that list.

For more information about this you can visit [this answer](https://standardjs.com/#i-use-a-library-that-pollutes-the-global-namespace-how-do-i-prevent-variable-is-not-defined-errors)
in the JavaScript Standard Style FAQ for more information.

### Useful E2E links

* [Cypress](https://www.cypress.io/)
* [Cypress documentation](https://docs.cypress.io/guides/overview/why-cypress.html#In-a-nutshell)
* [Guidelines for writing and organizing tests](https://docs.cypress.io/guides/core-concepts/writing-and-organizing-tests.html)
* [FAQ answer about ignoring functions in the global namespace](https://standardjs.com/#i-use-a-library-that-pollutes-the-global-namespace-how-do-i-prevent-variable-is-not-defined-errors)
