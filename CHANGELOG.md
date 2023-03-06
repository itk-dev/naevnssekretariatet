<!-- markdownlint-disable MD024 -->
# Changelog

![Keep a changelog badge](https://img.shields.io/badge/Keep%20a%20Changelog-v1.0.0-brightgreen.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiNmMTVkMzAiIHZpZXdCb3g9IjAgMCAxODcgMTg1Ij48cGF0aCBkPSJNNjIgN2MtMTUgMy0yOCAxMC0zNyAyMmExMjIgMTIyIDAgMDAtMTggOTEgNzQgNzQgMCAwMDE2IDM4YzYgOSAxNCAxNSAyNCAxOGE4OSA4OSAwIDAwMjQgNCA0NSA0NSAwIDAwNiAwbDMtMSAxMy0xYTE1OCAxNTggMCAwMDU1LTE3IDYzIDYzIDAgMDAzNS01MiAzNCAzNCAwIDAwLTEtNWMtMy0xOC05LTMzLTE5LTQ3LTEyLTE3LTI0LTI4LTM4LTM3QTg1IDg1IDAgMDA2MiA3em0zMCA4YzIwIDQgMzggMTQgNTMgMzEgMTcgMTggMjYgMzcgMjkgNTh2MTJjLTMgMTctMTMgMzAtMjggMzhhMTU1IDE1NSAwIDAxLTUzIDE2bC0xMyAyaC0xYTUxIDUxIDAgMDEtMTItMWwtMTctMmMtMTMtNC0yMy0xMi0yOS0yNy01LTEyLTgtMjQtOC0zOWExMzMgMTMzIDAgMDE4LTUwYzUtMTMgMTEtMjYgMjYtMzMgMTQtNyAyOS05IDQ1LTV6TTQwIDQ1YTk0IDk0IDAgMDAtMTcgNTQgNzUgNzUgMCAwMDYgMzJjOCAxOSAyMiAzMSA0MiAzMiAyMSAyIDQxLTIgNjAtMTRhNjAgNjAgMCAwMDIxLTE5IDUzIDUzIDAgMDA5LTI5YzAtMTYtOC0zMy0yMy01MWE0NyA0NyAwIDAwLTUtNWMtMjMtMjAtNDUtMjYtNjctMTgtMTIgNC0yMCA5LTI2IDE4em0xMDggNzZhNTAgNTAgMCAwMS0yMSAyMmMtMTcgOS0zMiAxMy00OCAxMy0xMSAwLTIxLTMtMzAtOS01LTMtOS05LTEzLTE2YTgxIDgxIDAgMDEtNi0zMiA5NCA5NCAwIDAxOC0zNSA5MCA5MCAwIDAxNi0xMmwxLTJjNS05IDEzLTEzIDIzLTE2IDE2LTUgMzItMyA1MCA5IDEzIDggMjMgMjAgMzAgMzYgNyAxNSA3IDI5IDAgNDJ6bS00My03M2MtMTctOC0zMy02LTQ2IDUtMTAgOC0xNiAyMC0xOSAzN2E1NCA1NCAwIDAwNSAzNGM3IDE1IDIwIDIzIDM3IDIyIDIyLTEgMzgtOSA0OC0yNGE0MSA0MSAwIDAwOC0yNCA0MyA0MyAwIDAwLTEtMTJjLTYtMTgtMTYtMzEtMzItMzh6bS0yMyA5MWgtMWMtNyAwLTE0LTItMjEtN2EyNyAyNyAwIDAxLTEwLTEzIDU3IDU3IDAgMDEtNC0yMCA2MyA2MyAwIDAxNi0yNWM1LTEyIDEyLTE5IDI0LTIxIDktMyAxOC0yIDI3IDIgMTQgNiAyMyAxOCAyNyAzM3MtMiAzMS0xNiA0MGMtMTEgOC0yMSAxMS0zMiAxMXptMS0zNHYxNGgtOFY2OGg4djI4bDEwLTEwaDExbC0xNCAxNSAxNyAxOEg5NnoiLz48L3N2Zz4K)

All notable changes to this project will be documented in this file.

See [keep a changelog](https://keepachangelog.com/en/1.0.0/) for information
about writing changes to this log.

## [Unreleased]

- [TVIST1-436](https://jira.itkdev.dk/browse/TVIST1-436)
  Made `CaseEvent.createdBy` not nullable.
  Marked `Document.originalFileName` as nullable
- [TVIST1-743](https://jira.itkdev.dk/browse/TVIST1-743)
  Added case events.
- [TVIST1-747](https://jira.itkdev.dk/browse/TVIST1-747)
  - Added suffix to document names when uploading multiple in one go.
  - Added ability to create case events when uploading documents.
  - Added ability to copy case events to other cases.
- [TVIST1-788](https://jira.itkdev.dk/browse/TVIST1-788)
  - Updated `CaseEvent` with `senders` and `recipients` property.
  - Removed `CaseEventPartyRelation`.
- Added user signatures [TVIST1-726](https://jira.itkdev.dk/browse/TVIST1-726)
- [TVIST1-436](https://jira.itkdev.dk/browse/TVIST1-436)
  Implemented Next Generation Digitapl Post (NgDP) and handling of
  Beskedfordeler messages.
- [TVIST1-436](https://jira.itkdev.dk/browse/TVIST1-436)
  Fixed CPR lookup.
- [TVIST1-789](https://jira.itkdev.dk/browse/TVIST1-789)
  Added `select2` to case selection when copying document or case event.

## [1.1.2] 2023-02-17

- [SUPP0RT-874](https://jira.itkdev.dk/browse/SUPP0RT-874)
  Handled invalid custom field element names.

## [1.1.1] 2023-02-15

- Bumped digital post max subject length to 50 characters

## [1.1.0] 2023-02-15

- Added check for updated changelog.
- [TVIST1-754](https://jira.itkdev.dk/browse/TVIST1-754)
  Added finished on logic to cases.
- [TVIST1-778](https://jira.itkdev.dk/browse/TVIST1-778)
  Showed correct time in frontend

## [1.0.7] 2023-02-09

- [TVIST1-752](https://jira.itkdev.dk/browse/TVIST1-752)
  Added pagination on document list.

## [1.0.6] 2023-02-06

- [SERV-628](https://jira.itkdev.dk/browse/SERV-628):
  Security fix.

## [1.0.5] 2023-01-06

- [TVIST1-735](https://jira.itkdev.dk/browse/TVIST1-735):
  - Sorted mail template choices alphabetically by name.
  - Added archiving logic to mail templates.

## [1.0.4] 2023-01-04

- [TVIST1-725](https://jira.itkdev.dk/browse/TVIST1-725):
  - Allow deletion of created hearing post requests and responses
  - Send hearing post response receipt upon approval rather than creation.
- [TVIST1-729](https://jira.itkdev.dk/browse/TVIST1-729):
  Allowed hearing to start with just a party or counterparty.

## [1.0.3] 2022-12-19

- [TVIST1-712](https://jira.itkdev.dk/browse/TVIST1-712):
  Allowed hearing to start without a counterparty.
- [TVIST1-706](https://jira.itkdev.dk/browse/TVIST1-706):
  Added checkbox giving the option to not send receipt upon
  creating hearing post response
- [TVIST1-673](https://jira.itkdev.dk/browse/TVIST1-673):
  Allowed configuration of mail template custom fields to
  either text or textarea.
- [TVIST1-713](https://jira.itkdev.dk/browse/TVIST1-713):
  Prepares for case created on behalf of information to come via OS2Forms.
- [TVIST1-723](https://jira.itkdev.dk/browse/TVIST1-723):
  Updated PDF generation to make it more accessible.
- [TVIST1-721](https://jira.itkdev.dk/browse/TVIST1-721):
  Stopped sending receipts upon case creation.

## [1.0.2] 2022-11-14

- [SUPP0RT-751](https://jira.itkdev.dk/browse/SUPP0RT-751): Fixed issue with
  missing country code in digital post

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

[Unreleased]: https://github.com/itk-dev/naevnssekretariatet/compare/1.1.2...HEAD
[1.1.2]: https://github.com/itk-dev/naevnssekretariatet/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/itk-dev/naevnssekretariatet/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.7...1.1.0
[1.0.7]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.6...1.0.7
[1.0.6]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/itk-dev/naevnssekretariatet/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/itk-dev/naevnssekretariatet/releases/tag/1.0.0
