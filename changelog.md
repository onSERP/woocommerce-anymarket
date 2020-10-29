# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0-beta.2 - 2020-10-29

### Fixed

-   Send variation values instead of product id
-   Anymarket script generationg console errors

### Changed

-   Moved exportVariation to its own class

## 1.0.0-beta.1 - 2020-10-27

### Changed

-   Updated order statuses labels
-   Optimized the way that product variation data is queried
-   Separated "show logs" option from "use sandbox" option
-         Set cron time to five seconds

### Added

-   Send variation images on product export
-   Send image orders
-   Created field on product attributes to store visual variation option
-   Added content on "Instructions" page
-   Create variation types on product export

### Fixed

-   Minor style fixes
-   Cleaning measure values before sending to anymarket
-   Execute category deletion script only on edit category page

### Removed

-   Removed all custom JS code in "View Order" page

## 1.0.0-beta 🎉 - 2020-10-16

### Changed

-   Separated wordpress notifications in a single file

### Added

-   Send price and sale price to Anymarket

## 1.0.0-alpha.5 - 2020-10-05

### Added

-   Added WP-Cron substitute in case of sites with modified wp installation.
-   Added self-hosted plugin updates.

### Changed

-   Changed cron task time from 5min to 1min.

### Fixed

-   Filtering orders that are not supposed to sync with anymarket.

## 1.0.0-alpha.4 - 2020-09-25

### Changed

-   Not relying on window.wp global object anymore. Using @wordrpess/dom-ready instead.

## 1.0.0-alpha.3 - 2020-09-16

### Changed

-   Turned order export into wp-cron cronjob.
-   Added support for wp_timezone_string() on WP <=5.3

## 1.0.0-alpha.2 - 2020-09-11

### Added

-   Translation file.

### Changed

-   Turned product export into wp-cron cronjob.
-   Turned product bulk export into wp-cron cronjob.

### Fixed

-   Only send brands if defined in config file.
-   Replaced "pa\_" on product variation names.
-   Missing translation strings.
-   "See on anymarket" button.

## 1.0.0-alpha.1 - 2020-08-31

### Changed

-   Return true on permissions callbacks of public rest route definitions.

## 1.0.0-alpha - 2020-08-01

-   🎉 First release!
-   Alpha version.
-   This is a simple yet powerful integration with ANYMARKET. More features to come!
