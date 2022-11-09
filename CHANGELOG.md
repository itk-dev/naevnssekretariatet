# Changelog
<!-- markdownlint-disable-next-line -->
![Keep a changelog badge](https://img.shields.io/badge/Keep%20a%20Changelog-v1.0.0-brightgreen.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiNmMTVkMzAiIHZpZXdCb3g9IjAgMCAxODcgMTg1Ij48cGF0aCBkPSJNNjIgN2MtMTUgMy0yOCAxMC0zNyAyMmExMjIgMTIyIDAgMDAtMTggOTEgNzQgNzQgMCAwMDE2IDM4YzYgOSAxNCAxNSAyNCAxOGE4OSA4OSAwIDAwMjQgNCA0NSA0NSAwIDAwNiAwbDMtMSAxMy0xYTE1OCAxNTggMCAwMDU1LTE3IDYzIDYzIDAgMDAzNS01MiAzNCAzNCAwIDAwLTEtNWMtMy0xOC05LTMzLTE5LTQ3LTEyLTE3LTI0LTI4LTM4LTM3QTg1IDg1IDAgMDA2MiA3em0zMCA4YzIwIDQgMzggMTQgNTMgMzEgMTcgMTggMjYgMzcgMjkgNTh2MTJjLTMgMTctMTMgMzAtMjggMzhhMTU1IDE1NSAwIDAxLTUzIDE2bC0xMyAyaC0xYTUxIDUxIDAgMDEtMTItMWwtMTctMmMtMTMtNC0yMy0xMi0yOS0yNy01LTEyLTgtMjQtOC0zOWExMzMgMTMzIDAgMDE4LTUwYzUtMTMgMTEtMjYgMjYtMzMgMTQtNyAyOS05IDQ1LTV6TTQwIDQ1YTk0IDk0IDAgMDAtMTcgNTQgNzUgNzUgMCAwMDYgMzJjOCAxOSAyMiAzMSA0MiAzMiAyMSAyIDQxLTIgNjAtMTRhNjAgNjAgMCAwMDIxLTE5IDUzIDUzIDAgMDA5LTI5YzAtMTYtOC0zMy0yMy01MWE0NyA0NyAwIDAwLTUtNWMtMjMtMjAtNDUtMjYtNjctMTgtMTIgNC0yMCA5LTI2IDE4em0xMDggNzZhNTAgNTAgMCAwMS0yMSAyMmMtMTcgOS0zMiAxMy00OCAxMy0xMSAwLTIxLTMtMzAtOS01LTMtOS05LTEzLTE2YTgxIDgxIDAgMDEtNi0zMiA5NCA5NCAwIDAxOC0zNSA5MCA5MCAwIDAxNi0xMmwxLTJjNS05IDEzLTEzIDIzLTE2IDE2LTUgMzItMyA1MCA5IDEzIDggMjMgMjAgMzAgMzYgNyAxNSA3IDI5IDAgNDJ6bS00My03M2MtMTctOC0zMy02LTQ2IDUtMTAgOC0xNiAyMC0xOSAzN2E1NCA1NCAwIDAwNSAzNGM3IDE1IDIwIDIzIDM3IDIyIDIyLTEgMzgtOSA0OC0yNGE0MSA0MSAwIDAwOC0yNCA0MyA0MyAwIDAwLTEtMTJjLTYtMTgtMTYtMzEtMzItMzh6bS0yMyA5MWgtMWMtNyAwLTE0LTItMjEtN2EyNyAyNyAwIDAxLTEwLTEzIDU3IDU3IDAgMDEtNC0yMCA2MyA2MyAwIDAxNi0yNWM1LTEyIDEyLTE5IDI0LTIxIDktMyAxOC0yIDI3IDIgMTQgNiAyMyAxOCAyNyAzM3MtMiAzMS0xNiA0MGMtMTEgOC0yMSAxMS0zMiAxMXptMS0zNHYxNGgtOFY2OGg4djI4bDEwLTEwaDExbC0xNCAxNSAxNyAxOEg5NnoiLz48L3N2Zz4K)

All notable changes to this project will be documented in this file.

See [keep a changelog](https://keepachangelog.com/en/1.0.0/) for information about writing changes to this log.

## [Unreleased]

## [1.0.1] 2022-11-09

### Changed

- [TVIST1-643](https://jira.itkdev.dk/browse/TVIST1-643): 
Changes on case to make it possible to select severalt complaint categories

### Fixed

- [DEVSUPP-1045](https://jira.itkdev.dk/browse/SUPP0RT-738):
Fixed error in PDF creation cause by unescaped characters
- [DEVSUPP-1047](https://jira.itkdev.dk/browse/SUPP0RT-740):
Fixed error in unescaped characters in filename

## [1.0.0] All released before 2022-11-08

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
- [NSEK-102](https://jira.itkdev.dk/browse/NSEK-104):
  Initial setup of assets.
- [NSEK-84](https://jira.itkdev.dk/browse/NSEK-84):
  Adding GitHub actions workflow for pull requests.
- [NSEK-109](https://jira.itkdev.dk/browse/NSEK-84):
  Adding documentation about translations.
- [NSEK-112](https://jira.itkdev.dk/browse/NSEK-112):
  Adding EasyAdmin bundle.
- [NSEK-105](https://jira.itkdev.dk/browse/NSEK-105):
  Added CLI and Azure B2C login.
- [TVIST1-120](https://jira.itkdev.dk/browse/TVIST1-120):
  Added modifiable default deadline on Board.
- [TVIST1-237](https://jira.itkdev.dk/browse/TVIST1-237):
  Logging documentation.
- [TVIST1-253](https://jira.itkdev.dk/browse/TVIST1-253):
  Added AbstractEntityListener, CaseListener,
    MunicipalityListener, BoardListener and their tests.
- [TVIST1-256](https://jira.itkdev.dk/browse/TVIST1-256):
  Added case number and CaseManager service.
- [TVIST1-264](https://jira.itkdev.dk/browse/TVIST1-264):
  Added fixtures.
- [TVIST1-184](https://jira.itkdev.dk/browse/TVIST1-184):
  Added ability to upload documents and showing
    uploaded documents in document index.
- [TVIST1-185](https://jira.itkdev.dk/browse/TVIST1-185):
  Added ability to 'soft' delete documents.
- [TVIST1-186](https://jira.itkdev.dk/browse/TVIST1-186):
  Added ability to copy documents to other cases.
- [TVIST1-126](https://jira.itkdev.dk/browse/TVIST1-126):
  Adding workflows for cases.
- [TVIST1-227](https://jira.itkdev.dk/browse/TVIST1-227):
  Added ability to create, edit and remove notes.
- [TVIST1-322](https://jira.itkdev.dk/browse/TVIST1-322):
  Replaced SubBoard entity by BoardRole entity.
- [TVIST1-306](https://jira.itkdev.dk/browse/TVIST1-306):
  Adding handling of case creation.
- [TVIST1-359](https://jira.itkdev.dk/browse/TVIST1-359):
  Added Case reminders.
- [TVIST1-238](https://jira.itkdev.dk/browse/TVIST1-238):
  Added RentBoardCase and FenceReviewCase.
- [TVIST1-410](https://jira.itkdev.dk/browse/TVIST1-410):
  Added address embeddable.
- [TVIST1-297](https://jira.itkdev.dk/browse/TVIST1-297):
  Added templates, routes and controller for Agenda module.
- [TVIST1-638](https://jira.itkdev.dk/browse/TVIST1-638):
  Added KLE to complaint category.

### Changed

- [NSEK-138](https://jira.itkdev.dk/browse/NSEK-138):
  Updating Cypress to 6.5.0 so the same version is used project wide.
- [TVIST1-144](https://jira.itkdev.dk/browse/TVIST1-144):
  Switched to `itk-dev/openid-connect-bundle` for AD and CLI login.
- [TVIST1-670](https://jira.itkdev.dk/browse/TVIST1-670):
  Updated `${agenda.items}` template macro.
- [TVIST1-643](https://jira.itkdev.dk/browse/TVIST1-643):
  Allowed multiple complaint categories on cases.

### Fixed

- [NSEK-104](https://jira.itkdev.dk/browse/NSEK-104):
  Added missing database variables to .env file.
- [TVIST1-254](https://jira.itkdev.dk/browse/TVIST1-254):
  Removed trailing slash in redirect URL.
- [TVIST1-604](https://jira.itkdev.dk/browse/TVIST1-604):
  Resolved issue regarding time formats.

[Unreleased]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.1...HEAD
[1.0.1]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/itk-dev/naevnssekretariatet/releases/tag/1.0.0
