# Contributing

This document describes various processes and information
when contributing to this project.

## The birth of a feature

Most features in this project are developed with a user story,
and a wireframe as starting point.
These user stories are described as tasks in our project management tool Jira,
and the wireframes are usually drawn in Whimsical.

## Definition of done

Other than fulfilling its use case, a feature is considered done when
the following requirements are met.

* Written tests covering the feature.
* Adhered to the coding standards.
* Documented the feature.
* Added an entry to the changelog.
* Updated the README with details of changes to the interface,
  this includes new environment variables, useful file locations and container parameters.

## Development

The [Git Flow branching model](https://nvie.com/posts/a-successful-git-branching-model/)
is used as our branching model.

### Coding standards

The following coding standards are enforced in this project:

* PHP - [Symfony Coding Standards](https://symfony.com/doc/5.2/contributing/code/standards.html)
* Twig - [Twig Coding Standards](https://twig.symfony.com/doc/3.x/coding_standards.html)
* Javascript - [Javascript Standard Style](https://standardjs.com/rules.html)
* Markdown - [markdownlint rules](https://github.com/DavidAnson/markdownlint/blob/main/doc/Rules.md)

### Naming branches

The Git Flow branching model already describes the basics of naming branches.
In this project the branches also includes the shortname of the task in Jira.

A branch should be names as follows:

```sh
type-of-task/JIRA-SHORT-NAME-title-of-task
```

Examples:

```sh
feature/NSEK-84-creating-prject
hotfix/SUPPORT-92-fix-missing-variable
```

### Writing commit messages

A general guideline for writing commit messages is that the message should describe
which problem or use case it solves, and should be meaningful for
other persons that yourself.

Other than that the commit message should start with the shortname
of the corresponding Jira task.

Example:

```sh
NSEK-84: Creating project
```

## Pull Request Process

Before opening a pull request, make sure that you have followed
the definition of done described elsewhere in this document.

1. Create your feature branch (`git checkout -b feature/AmazingFeature`)
2. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
3. Push to the Branch (`git push origin feature/AmazingFeature`)
4. Open a Pull Request
5. You may merge your Pull Request when another team member has reviewed and
   approved your request.
