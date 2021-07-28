# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.1.1 - 2021-07-28

### Fixed

-   Protect ExportStock::exportFromOrder() from getting external orders
-   Logging new error when disconting stock from external orders

## 1.1.0 - 2021-07-08

### Fixed

-   Failing mass export in cron events.
-   Admin scripts throwing error on settings page

### Added

-   PHP console logger function
-   Logging cron schedule events
-   Option to enable/disable cron events
-   Option in product to enable/disable images sync

### Changed

-   Bump ssri from 6.0.1 to 6.0.2
-   Bump handlebars from 4.7.6 to 4.7.7
-   Bump url-parse from 1.4.7 to 1.5.1
-   Bump hosted-git-info from 2.8.8 to 2.8.9
-   Bump lodash from 4.17.20 to 4.17.21
-   Bump dns-packet from 1.3.1 to 1.3.4
-   Bump ws from 6.2.1 to 6.2.2
-   Bump htmlburger/carbon-fields from 3.1 to 3.3.2
-   Bump color-string from 1.5.4 to 1.5.5
-   Bump php-curl from dev-master to 8.9.3

## 1.0.0 🎉 - 2021-04-12

### Fixed

-   Required field `_billing_persontype` wasn't being sent
-   Added missing shipping fields to order

## 1.0.0-beta.6 - 2021-01-15

### Added

-   Added option to update all products that are already synced

### Fixed

-   Stock sync fixes: filtering by status.

## 1.0.0-beta.5 - 2020-11-20

### Added

-   Edit mode: allow user to associate products by changing its ID

## 1.0.0-beta.4 - 2020-11-18

### Added

-   Get shipping methods and values within the order
-   Stock only option

## 1.0.0-beta.3 - 2020-11-08

### Fixed

-   Removed brands panel when not necessary form plugin config page
-   Duplicated buttons

### Changed

-   Using `$wpdb` instead of `get_posts` since it's more failproof

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
-   Set cron time to five seconds

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
-   Added support for `wp_timezone_string()` on WP <=5.3

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
