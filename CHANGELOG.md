# Changes

## 2.1.6

* [module:quote] expose client infos to invoice templates
* [module:resources] fix resources events edition
* [module:booking] add controls to avoid mktime typeErrors
* [module:invoice] add missing casts to invoice related controllers
* [module:booking] fix BkAccess::set missing id_space
* [module:invoice] expose client infos to invoice templates

## 2.1.5

* [module:antibodies] fix antibody number incrementation (need to follow, by space)
* [module:services] fix invoice by project
* [module:booking] set resources qtes default value to empty string
* [module:catalog] fix missing id_space
* [module:booking,service,stats] fix mktime errors

## 2.1.4

* fix invoicing units
* [excel] use Xlsx instead of 2007 writer
* [module:booking][stats] fix sql and unit ref for stats
* [module:booking] fix phpoffice worksheet calls
* [sql] fix ReEvent:set sql missing id_space
* [module:booking] fix generateStats (missing id_space)
* [sql] fix sql request on revisa (PFM-1X)
* [module:booking] if start/end minutes are empty, set to 0
* [module:clients] fix clients company settings when not found in db (PFM-1M)
* [module:services] fix invoice call with wrong SQL (PFM-1S)
* [module:projects] fix project dates parsing (PFM-1Q)
* [sql] fix sql request on document, missing space id (PFM-1P)

## 2.1.3

* [db] fix upgrade_v2_v3 migration script on bk_authorizations
  **Warning**: if you installed release >=2.1 < 2.1.3 you need to run a db fix script
  php cli/pfm-cli.php repair --bug 332
* [sql] fix ReEventType getName sql request
* fix call to getSpaceActiveUsers in resourcesinfo respsAction

## 2.1.2

* day area dayafter and daybefore actions fixed, closes #326, closes #299
* Fix servicesprojects edition #324
* [booking] getSpaceActiveUsersForSelect, order users by name Closes #323
* fix booking calendar display Closes #322
* add id_space to ClAddress::set() (#320)

## 2.1.1

* [helpdesk] fix remind call
* [deps] fix influxdb/guzzle deps versions Closes #318
* [cli] fix fresh install detection
* Update BkBookingTableCSS.php, add id_space to getAreaCss()
* [helpdesk] if mail not for a space, skip

## 2.1

* fix resources visa listing
* #234 fix defaults in db schema
* #230 generate apikey on ldap login
* #233 fix schema of core_j_spaces_user
* #218 allow user to define default language
* Add prometheus statistics
* #225 bug fixes
* #235 use static data for se_service_types
* #246 allow user self registration (via config)
* #244 Allow impersonation
* #142 fix resource with no category handling
* #272 allow to display news in popup on space home page
* #275 Fix color codes error in booking schedulings 
* #292 [ServicesinvoiceorderController] calls to deprecated function createByUnitForm()
* #298 Add invoice by quantities
* #281 Add front-end controls in user forms. Improves ergonomy
* #302 fix_pendings_users_couldnt_unsubscribe

## 2.0.5

* Fix #293 quote edition not working

## 2.0.4

* Fix admin space to edit space
* Fix clientuserdelete params
* Add user email to notification for join requests and to pending users accounts list

## 2.0.3

* Bug fix release (routes on antibodies and history)

## 2.0.2

* fix servicesprojecteditentryquery route parameters #253
* add route reload command in cli
* Fix antibodieslist action parameter name (route expects sortentry but function defines letter)

## 2.0.1

* #245 fix count calls on int

## 2.0

* Fix multi-tenant (still have pending issues for some modules,
  will be fixed in next release)
* Fix multi-tenant issues for email
* #206 fix booking invoices creation when night and weekend
  prices option not set
* #189 fix booking blocking errors
* #194 fix client creation
* #105 fix user modification
* #171 fix user login inactivity
* #168 fix menu board display
* Add sql debug and logging

* New command-line script cli/php-cli.php:
  * used for install/upgrade commands

* New env variables:
  * DEBUG: activate logs with debug level
  * DEBUG_SQL: activate sql logs with debug level
  * DEBUG_INFLUXDB: if using influxdb, set debug log level
  * PFM_MODE: [prod(default)|dev|test] in test, activate browser dev tab,
    in mode test disable html rendering and return template vars on controller call.
  * PFM_ADMIN_EMAIL: default super admin contact email
  * PFM_ADMIN_PASSWORD: default super admin user password (install only)
  * PFM_AMQP_HOST, PFM_AMQP_PORT, PFM_AMQP_USER, PFM_AMQP_PASSWORD: rabbitmq settings (optional)
  * PFM_INFLUXDB_URL, PFM_INFLUXDB_TOKEN, PFM_INFLUXDB_ORG: influxdb settings

* New config parameters:
  * same as env variables, without PFM_PREFIX and in lowercase
