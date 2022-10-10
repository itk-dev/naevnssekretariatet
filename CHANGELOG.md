# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- This CHANGELOG file to hopefully serve as an evolving example of a
  standardized open source project CHANGELOG.
- Standard Symfony 5.2 project
- Docker-compose development setup
- README
- LICENSE
- PHP-CS-Fixer
- Twigcs
- PHPUnit
- Encore
- Standard
- markdownlint
- AliceBundle
- Documentation files
- Cypress
- Testing documentation
- Contributing documentation
- [NSEK-102](https://jira.itkdev.dk/browse/NSEK-104) Initial setup of assets.
- [NSEK-84](https://jira.itkdev.dk/browse/NSEK-84): Adding GitHub actions workflow for pull requests.
- [NSEK-109](https://jira.itkdev.dk/browse/NSEK-84): Adding documentation about translations.
- [NSEK-112](https://jira.itkdev.dk/browse/NSEK-112): Adding EasyAdmin bundle.
- [NSEK-105](https://jira.itkdev.dk/browse/NSEK-105): Added CLI and Azure B2C login.
- [TVIST1-120](https://jira.itkdev.dk/browse/TVIST1-120): Added modifiable default deadline on Board.
- [TVIST1-237](https://jira.itkdev.dk/browse/TVIST1-237): Logging documentation.
- [TVIST1-253](https://jira.itkdev.dk/browse/TVIST1-253): Added AbstractEntityListener, CaseListener,
MunicipalityListener, BoardListener and their tests.
- [TVIST1-256](https://jira.itkdev.dk/browse/TVIST1-256): Added case number and CaseManager service.
- [TVIST1-264](https://jira.itkdev.dk/browse/TVIST1-264): Added fixtures.
- [TVIST1-184](https://jira.itkdev.dk/browse/TVIST1-184): Added ability to upload documents and showing
  uploaded documents in document index.
- [TVIST1-185](https://jira.itkdev.dk/browse/TVIST1-185): Added ability to 'soft' delete documents.
- [TVIST1-186](https://jira.itkdev.dk/browse/TVIST1-186): Added ability to copy documents to other cases.
- [TVIST1-126](https://jira.itkdev.dk/browse/TVIST1-126): Adding workflows for cases.
- [TVIST1-227](https://jira.itkdev.dk/browse/TVIST1-227): Added ability to create, edit and remove notes.
- [TVIST1-322](https://jira.itkdev.dk/browse/TVIST1-322): Replaced SubBoard entity by BoardRole entity.
- [TVIST1-306](https://jira.itkdev.dk/browse/TVIST1-306): Adding handling of case creation.
- [TVIST1-359](https://jira.itkdev.dk/browse/TVIST1-359): Added Case reminders.
- [TVIST1-238](https://jira.itkdev.dk/browse/TVIST1-238): Added RentBoardCase and FenceReviewCase.
- [TVIST1-410](https://jira.itkdev.dk/browse/TVIST1-410): Added address embeddable.
- [TVIST1-297](https://jira.itkdev.dk/browse/TVIST1-297): Added templates, routes and controller for Agenda module.

### Changed
- [NSEK-138](https://jira.itkdev.dk/browse/NSEK-138): Updating Cypress to 6.5.0 so the same version is used project wide.
- [TVIST1-144](https://jira.itkdev.dk/browse/TVIST1-144): Switched to `itk-dev/openid-connect-bundle` for AD and CLI login.
- [TVIST1-670](https://jira.itkdev.dk/browse/TVIST1-670): Updated `${agenda.items}` template macro.

### Fixed
- [NSEK-104](https://jira.itkdev.dk/browse/NSEK-104): Added missing database variables to .env file.
- [TVIST1-254](https://jira.itkdev.dk/browse/TVIST1-254): Removed trailing slash in redirect URL
