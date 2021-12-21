# Changes

## 2.2.2

* [booking] fix authorizations when related to user's roles

## 2.2.1

* [booking] fix another booking authorizations bug
* [helpdesk] sort tickets and fix refresh
* [helpdesk] ignore auto replies
* [core] check id_user to see if user is logged
* [core] on register after email validation validate the account #472
* [statistics] require env var MYSQL_ADMIN_PWD: ${MYSQL_ROOT_PASSWORD} in pfm/pfm-events containers
* [core] use module url and not name for notifications #476
* [cli][core] update way to expire users, and on delete just anon user

## 2.2.0

**Warning**: [admin] Ldap configuration modification from ini or env variables
only, not configurable via UI anymore and existing values not taken into account.
See ldap.ini.example if needed or doc for env variables.

* [coreTiles] fix join button display
* [booking] fix booking authorizations bug
* [booking] fix booking display if no bk_scheduling set
* [CoreTiles] fix display
* [statistics] fix service/projects statistics, invalid responsible and
 add count of projects per client and responsible (in charge) #455
* [booking] avoid any resource selection in resources input
* [booking] Fix reservation form unexpected submissions
* [booking] fix bk_scheduling fetching from booking view
* [self_registration] add structures names to listed spaces
* [clients] fix company infos
* [documents] Avoid missing files opening
* [documents] fix documents duplication on edition
* [core] Remove references to ecUnit, ecBelonging and ecUser
* [booking][email] Add userName in emails sent to resources managers
* [core][email] Add userName in emails sent to admins
* [core][formAdd] fix last row deletion
* [booking] fix bk_calsupinfo mandatory column name which caused errors on supplementaries info
* [core][email] fix from header when helpdesk not activated
* [core][ldap] get all config from ini files or env variables
* [Menus] Change word "Menu" to "Structure"
* [forms] prevent errors on form submissions
* [Documentation] Add quote documentation
* [module:quote] improve new user quote interface
* [module:quote] expose client infos to invoice templates
* [Services] Remove deprecated functions from servicesController
* [Exceptions] Add PfmUserException class
* [Users] Remove login from edit function
* [self_registration] fix selfregistration email sending order
* [Security] Add missing access authorization controls
* [Users] Improve users creation forms controls
* [resources] set default booking authorizations at resource creation
* [module:booking_settings] fix display edition
* [stats] count number of tickets per status
* [stats] add stat calentry_cancel on booking cancel
* [mail] allow users to unsubscribe to notifications #382
* [core][ldap] rename base config parameters for ldap auth
* [helpdesk] ignore delivery status notifications (do not reply)
* [helpdesk] let user select multiple tickets to spam them #393
* [core] limit file uploads name to alphanumeric # 402
* [core] add welcome page and use it as default entry url
* [core] manage db reconnection in case of failure
* [core] on welcome page show different use info

## 2.1.9

* [users] fix users_info sql
* [helpdesk] check if message is an auto-reply and log
* [booking][invoice] fix sql
* [booking][colorcode] fix sql
* [sql] fix wrong space_id error => id_space (param and sql)
* [booking][calsup] fix remove unlisted supinfo

## 2.1.8

* [users] change checkunicity routes
* [coreInstall] add repair371() function
* [module:users] add *Organization* and *Unit* to users listing arrays
* [self_registration] add *Organization* and *Unit* inputs
* [self_registration] add login suggestion
* [self_registration] add unicity checks to login and email inputs
* [self_registration] fix email sent to space admins

## 2.1.7

* [module:booking] fix supplementaries deletion
* [module:invoices] fix set floats for items quantities
* [module:booking] fix BkCalendarPeriod missing id_space
* [helpdesk] fix list of tickets and mail origin
* [configuration] use env var SMTP_FROM instead of MAIL_FROM (deprecated)
* [module:booking] for mails check user still have a role in space #383

## 2.1.6

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
* [cli] fix fresh install detection
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
