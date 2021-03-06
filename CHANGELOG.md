# Changelog

## [3.5.0] 2021-02-24

### Added

- Added seconds option to queue:worker command to set the amount of maximum time that the queue worker daemon should run for

## [3.4.2] 2021-02-24

### Changed

- Changed Schedule::run to work with changed exceptions

### [3.4.0] 2021-02-24

### Added

- Added Schedule::run

## [3.3.0] 2021-01-14

### Fixed

- Fixed PHP 8 Issues

## [3.2.4] 2021-01-14

### Changed

- Added option to use version 2.0 of the plugin installer

## [3.2.3] 2021-01-04

## [3.2.2] 2020-08-15

### Changed

- Changed `DbMigrateCommand` to also scan `Plugin` directories

### Removed

- Removed default value for message from `MaintenanceStartCommand`

## [3.2.1] - 2020-07-19

### Changed

- Changed `QueueWorkerCommand` to not process jobs when in maintenance mode
- Changed `MailboxDownloadCommand` to not download email when in maintenance mode

## [3.2.0] - 2020-07-18

### Added

- Added `maintenance:start` command
- Added `maintenance:end` command

## [3.1.0] - 2020-07-13

### Changed

- Changed dependency to framework, as I have decided to hold back on decoupling until the current subtree split process can be ensured to be 100% reliable.

## [3.0.1] - 2020-07-09

### Fixed

- Fixed bootstrap path
- Fixed missing dependency

## [3.0.0] - 2020-07-08

### Changed

- Changed OriginPHP framework minimum 3.0
- Changed PHP minimum 7.3
- Changed PHPUnit minimum 9.2

- Changed `Cache::clear` to remove `.env.php`

### Removed

- Removed deprecation notices

## [2.0.0]

Skipped this version

## [1.4.3] - 2020-07-03

### Fixed

- Fixed deprecation notices display

## [1.4.2] - 2020-07-03

### Added

- Added deprecation warning for deprecated settings from older apps

## [1.4.1] - 2020-06-17

### Fixed

- Fixed db:test:prepare not removing sqlite database

## [1.4.0] - 2020-06-14

### Added

- Added support for SQlite
- Added cache clearing after database activities such as migrations

## [1.3.0] - 2020-05-10

### Added

- Added check for App.schemaFormat to work with version 2.6

## [1.2.0] - 2019-11-23

### Added

- Added mailbox:download command

## [1.1.0] - 2019-11-06

### Added

- Added cache:clear command

## [1.0.0] - 2019-10-18

These console commands have been decoupled from the [OriginPHP framework](https://www.originphp.com/).
