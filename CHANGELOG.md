<!-- markdownlint-disable MD024 -->
# Changelog

![Keep a changelog badge](https://img.shields.io/badge/Keep%20a%20Changelog-v1.0.0-brightgreen.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiNmMTVkMzAiIHZpZXdCb3g9IjAgMCAxODcgMTg1Ij48cGF0aCBkPSJNNjIgN2MtMTUgMy0yOCAxMC0zNyAyMmExMjIgMTIyIDAgMDAtMTggOTEgNzQgNzQgMCAwMDE2IDM4YzYgOSAxNCAxNSAyNCAxOGE4OSA4OSAwIDAwMjQgNCA0NSA0NSAwIDAwNiAwbDMtMSAxMy0xYTE1OCAxNTggMCAwMDU1LTE3IDYzIDYzIDAgMDAzNS01MiAzNCAzNCAwIDAwLTEtNWMtMy0xOC05LTMzLTE5LTQ3LTEyLTE3LTI0LTI4LTM4LTM3QTg1IDg1IDAgMDA2MiA3em0zMCA4YzIwIDQgMzggMTQgNTMgMzEgMTcgMTggMjYgMzcgMjkgNTh2MTJjLTMgMTctMTMgMzAtMjggMzhhMTU1IDE1NSAwIDAxLTUzIDE2bC0xMyAyaC0xYTUxIDUxIDAgMDEtMTItMWwtMTctMmMtMTMtNC0yMy0xMi0yOS0yNy01LTEyLTgtMjQtOC0zOWExMzMgMTMzIDAgMDE4LTUwYzUtMTMgMTEtMjYgMjYtMzMgMTQtNyAyOS05IDQ1LTV6TTQwIDQ1YTk0IDk0IDAgMDAtMTcgNTQgNzUgNzUgMCAwMDYgMzJjOCAxOSAyMiAzMSA0MiAzMiAyMSAyIDQxLTIgNjAtMTRhNjAgNjAgMCAwMDIxLTE5IDUzIDUzIDAgMDA5LTI5YzAtMTYtOC0zMy0yMy01MWE0NyA0NyAwIDAwLTUtNWMtMjMtMjAtNDUtMjYtNjctMTgtMTIgNC0yMCA5LTI2IDE4em0xMDggNzZhNTAgNTAgMCAwMS0yMSAyMmMtMTcgOS0zMiAxMy00OCAxMy0xMSAwLTIxLTMtMzAtOS01LTMtOS05LTEzLTE2YTgxIDgxIDAgMDEtNi0zMiA5NCA5NCAwIDAxOC0zNSA5MCA5MCAwIDAxNi0xMmwxLTJjNS05IDEzLTEzIDIzLTE2IDE2LTUgMzItMyA1MCA5IDEzIDggMjMgMjAgMzAgMzYgNyAxNSA3IDI5IDAgNDJ6bS00My03M2MtMTctOC0zMy02LTQ2IDUtMTAgOC0xNiAyMC0xOSAzN2E1NCA1NCAwIDAwNSAzNGM3IDE1IDIwIDIzIDM3IDIyIDIyLTEgMzgtOSA0OC0yNGE0MSA0MSAwIDAwOC0yNCA0MyA0MyAwIDAwLTEtMTJjLTYtMTgtMTYtMzEtMzItMzh6bS0yMyA5MWgtMWMtNyAwLTE0LTItMjEtN2EyNyAyNyAwIDAxLTEwLTEzIDU3IDU3IDAgMDEtNC0yMCA2MyA2MyAwIDAxNi0yNWM1LTEyIDEyLTE5IDI0LTIxIDktMyAxOC0yIDI3IDIgMTQgNiAyMyAxOCAyNyAzM3MtMiAzMS0xNiA0MGMtMTEgOC0yMSAxMS0zMiAxMXptMS0zNHYxNGgtOFY2OGg4djI4bDEwLTEwaDExbC0xNCAxNSAxNyAxOEg5NnoiLz48L3N2Zz4K)

All notable changes to this project will be documented in this file.

See [keep a changelog](https://keepachangelog.com/en/1.0.0/) for information
about writing changes to this log.

## [Unreleased]

## [1.7.1] - 2025-05-28

- [PR-410](https://github.com/itk-dev/naevnssekretariatet/pull/410)
  - Updates to latest `itk-dev/serviceplatformen` to ensure latest endpoints
  are used when fetching SAML-token.
  - Updates GitHub Actions.
- [PR-409](https://github.com/itk-dev/naevnssekretariatet/pull/409)
  Updated GitHub workflow images

## [1.7.0] - 2025-01-20

- [PR-407](https://github.com/itk-dev/naevnssekretariatet/pull/407)
  Sort documents chronologically after upload date in agenda item routes.

## [1.6.2] - 2024-12-17

- [PR-405](https://github.com/itk-dev/naevnssekretariatet/pull/405)
  Digital post fixes

## [1.6.1] - 2024-10-10

- [PR-404](https://github.com/itk-dev/naevnssekretariatet/pull/404)
  Change max allowed upload size in nginx server config

## [1.6.0] - 2024-10-10

- [PR-402](https://github.com/itk-dev/naevnssekretariatet/pull/402)
  Optimized handling of digital post
- [PR-401](https://github.com/itk-dev/naevnssekretariatet/pull/401)
  Update itk-dev/serviceplatformen
- [PR-399](https://github.com/itk-dev/naevnssekretariatet/pull/399)
  Added `forsendelse-uuid` filtering option to
  `digital-post-envelope:list` command.

## [1.5.6] 2024-05-28

- [PR-396](https://github.com/itk-dev/naevnssekretariatet/pull/396)
  Filtered out finished cases when creating agenda case items.
- [PR-397](https://github.com/itk-dev/naevnssekretariatet/pull/397)
  Updated MeMo message sender label.

## [1.5.5] 2024-04-29

- [PR-394](https://github.com/itk-dev/naevnssekretariatet/pull/394)
  Added logout functionality.
- [PR-393](https://github.com/itk-dev/naevnssekretariatet/pull/393)
  Fixed `BoardRole` crud search property.
- [PR-392](https://github.com/itk-dev/naevnssekretariatet/pull/392)
  Updated translations.
- [PR-391](https://github.com/itk-dev/naevnssekretariatet/pull/391)
  Updated composer packages
- [PR-377](https://github.com/itk-dev/naevnssekretariatet/pull/377)
  Improved `tvist1:digital-post-envelope:list` command and added Digital post
  debug command.
- [PR-382](https://github.com/itk-dev/naevnssekretariatet/pull/382)
  Added `--digital-post-id` filter on `tvist1:digital-post-envelope:list`.
- [PR-380](https://github.com/itk-dev/naevnssekretariatet/pull/380)
  Updated `itk-dev/openid-connect-bundle` to use authorization code flow.
- [PR-380](https://github.com/itk-dev/naevnssekretariatet/pull/380)
  Added [OpenId Connect Server
  Mock](https://github.com/Soluto/oidc-server-mock) for mocking
  OIDC IdPs during development.

## [1.5.4] 2024-04-03

- [PR-384](https://github.com/itk-dev/naevnssekretariatet/pull/384)
  Added template placeholder relation data to recipient
  prefix when creating hearing briefings.

## [1.5.3] 2023-12-19

- [PR-377](https://github.com/itk-dev/naevnssekretariatet/pull/377)
  Improved `tvist1:digital-post-envelope:list` command and added Digital post
  debug command.

## [1.5.2] 2023-07-13

- [PR-378](https://github.com/itk-dev/naevnssekretariatet/pull/378)
  Updated BBR meddelelse url.

## [1.5.1] 2023-07-03

- [PR-375](https://github.com/itk-dev/naevnssekretariatet/pull/375)
  Use document on load rather than ready.

## [1.5.0] 2023-05-22

- [PR-373](https://github.com/itk-dev/naevnssekretariatet/pull/373)
  Updated briefing logic to avoid attaching own hearing letter
- [PR-369](https://github.com/itk-dev/naevnssekretariatet/pull/369)
  Added logic for briefing parties during hearing
- [PR-356](https://github.com/itk-dev/naevnssekretariatet/pull/356)
  Updated hearing data structure to allow multiple recipients
- [PR-370](https://github.com/itk-dev/naevnssekretariatet/pull/370) Added
  digital post message transport and tuned retry intervals.
- Cleaned up translations.
- [PR-368](https://github.com/itk-dev/naevnssekretariatet/pull/368)
  Added `message-uuid` filter on `tvist1:digital-post-envelope:list` command.
- [PR-367](https://github.com/itk-dev/naevnssekretariatet/pull/367)
  Preselected party relation type upon edit
- [PR-363](https://github.com/itk-dev/naevnssekretariatet/pull/363)
  Validate party identifier based on identifier type
- [PR-366](https://github.com/itk-dev/naevnssekretariatet/pull/366)
  Removed `InspectionLetter` and its logic.
- [PR-364](https://github.com/itk-dev/naevnssekretariatet/pull/364)
  Removes button for creating `HearingPostResponse` and `Decision`,
  and receipt logic.

## [1.4.1] 2023-04-25

- [PR-365](https://github.com/itk-dev/naevnssekretariatet/pull/365)
  Handled display of broadcast digital post
- [PR-360](https://github.com/itk-dev/naevnssekretariatet/pull/360)
  Fixed type.
- [PR-361](https://github.com/itk-dev/naevnssekretariatet/pull/361)
  Update accordions

## [1.4.0] 2023-04-25

- [PR-358](https://github.com/itk-dev/naevnssekretariatet/pull/358)
  Set post-kategori-kode in digital post.
- [PR-357](https://github.com/itk-dev/naevnssekretariatet/pull/357)
  Properly displays mail template custom data
- [PR-355](https://github.com/itk-dev/naevnssekretariatet/pull/355)
  Added restart setting to libreoffice-api service
- [PR-354](https://github.com/itk-dev/naevnssekretariatet/pull/354)
  Downgraded `symfony/maker-bundle` to ensure support for annotations
- [PR-353](https://github.com/itk-dev/naevnssekretariatet/pull/353)
  Adds `referenceNumber` to `CasePartyRelation`
  and usage of this during hearing
- [PR-352](https://github.com/itk-dev/naevnssekretariatet/pull/352)
  Fixes digital post creation for inspection letters
- [PR-350](https://github.com/itk-dev/naevnssekretariatet/pull/350)
  Update dependenscies and migrate bootstrap from v4 to v5
- [PR-346](https://github.com/itk-dev/naevnssekretariatet/pull/346)
  Frontend improvements

## [1.3.2] 2023-03-30

- [TVIST1-809](https://jira.itkdev.dk/browse/TVIST1-809)
  Fixed stupid error.

## [1.3.1] 2023-03-30

- [TVIST1-809](https://jira.itkdev.dk/browse/TVIST1-809)
  Digital post fixes and improvements.

## [1.3.0] 2023-03-29

- [TVIST1-807](https://jira.itkdev.dk/browse/TVIST1-807)
  Improved digital post logging and handled "fjernprint"
- [TVIST1-755](https://jira.itkdev.dk/browse/TVIST1-755)
  Digital post fixes
- [TVIST1-755](https://jira.itkdev.dk/browse/TVIST1-755)
  Updated docker compose setup
- [TVIST1-755](https://jira.itkdev.dk/browse/TVIST1-755)
  Moved custom project docker compose settings into override files
- [TVIST1-809](https://jira.itkdev.dk/browse/TVIST1-809)
  Made commands a little more useful.
- [TVIST1-812](https://jira.itkdev.dk/browse/TVIST1-812)
  Added default `CLI_REDIRECT` to `.env`.
- [TVIST1-810](https://jira.itkdev.dk/browse/TVIST1-810)
  Fixes document deletion issue

## [1.2.0] 2023-03-15

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
- [TVIST1-436](https://jira.itkdev.dk/browse/TVIST1-436)
  - Removed defunct command and removed digital post status property.
  - Added display of digital post statuses.
- [TVIST1-436](https://jira.itkdev.dk/browse/TVIST1-436)
  Skipped storing MeMo file content in database.
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

[Unreleased]: https://github.com/itk-dev/naevnssekretariatet/compare/1.7.0...HEAD
[1.7.1]: https://github.com/itk-dev/naevnssekretariatet/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/itk-dev/naevnssekretariatet/compare/1.6.2...1.7.0
[1.6.2]: https://github.com/itk-dev/naevnssekretariatet/compare/1.6.1...1.6.2
[1.6.1]: https://github.com/itk-dev/naevnssekretariatet/compare/1.6.0...1.6.1
[1.6.0]: https://github.com/itk-dev/naevnssekretariatet/compare/1.5.6...1.6.0
[1.5.6]: https://github.com/itk-dev/naevnssekretariatet/compare/1.5.5...1.5.6
[1.5.5]: https://github.com/itk-dev/naevnssekretariatet/compare/1.5.4...1.5.5
[1.5.4]: https://github.com/itk-dev/naevnssekretariatet/compare/1.5.3...1.5.4
[1.5.3]: https://github.com/itk-dev/naevnssekretariatet/compare/1.5.2...1.5.3
[1.5.2]: https://github.com/itk-dev/naevnssekretariatet/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/itk-dev/naevnssekretariatet/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/itk-dev/naevnssekretariatet/compare/1.4.1...1.5.0
[1.4.1]: https://github.com/itk-dev/naevnssekretariatet/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/itk-dev/naevnssekretariatet/compare/1.3.2...1.4.0
[1.3.2]: https://github.com/itk-dev/naevnssekretariatet/compare/1.3.1...1.3.2
[1.3.1]: https://github.com/itk-dev/naevnssekretariatet/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/itk-dev/naevnssekretariatet/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/itk-dev/naevnssekretariatet/compare/1.1.2...1.2.0
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
