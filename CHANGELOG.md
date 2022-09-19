# Changes

## 2.8.3

### Fixes

* [booking] remove deleted supinfos fetch in reservation edition, closes #712

## 2.8.2

### Fixes

* [services] fix/enhance order services management (add/remove) combining multiple lines
  or removing empty quantities

## 2.8.1

### Fixes

* [helpdesk] error in helpdesk web ui
* [services] fix order update which could delete a service from all orders, or fail to remove element

## 2.8.0

### Docker

* Add specific rabbitmq config in docker-compose to increase max background process duration;
  sets consumer_timeout to 2h in /etc/rabbitmq/conf.d/pfm.conf

### Features / enhancements

* [booking] add calendar default view option in booking config
* [booking] allow to delete bookingAuthorisations
* [antibodies] add reactivity attribute
* [doc] general update, add missing modules documentation
* [coreconnexion] add email confirmation at account creation
* [booking] add month to default booking views in user's settings
* [invoice] for global invoice, fix discount display
* [invoice] speedup pdf generation with details (sql refactoring)
* [clients] make address forms facultative

### Fixes

* [core] fix invalid check on core pending accounts
* [com] fix news popup display on space welcome page
* [invoices] fix global invoice if no order is to be charged (Closes #688)
* [bin/] close database connexion after each message/run
* [clients] on client removal, remove user/client link
* [quotes] show old client account used for a quote, in quote edition in case user switched to new client
* [services] show old client account used for a order/project, in order/project edition in case user switched to new client
* [services/clients] prevent pricing removal if used by a client
* [booking] keep deleted packages/supplementaries/quantities infos in existing reservations
* [invoices] allow to invoice existing reservations with deleted packages or invoicable quantities
* [invoice] manage invoicing/display in case of deleted service
* [services] fix orders edition form
* [com] avoid displaying news popup if no news
* [catalog] fix catalog public page header file upload
* [booking] fix booking delete sendemail
* [invoices] in global invoices, some service names prevented invoice edit or discount update
* [events] fix disconnection from rabbitmq

## 2.7.1

### Fixes

* [services] add missing base columns on se_project_user_table on upgrade
* [core] fix role checks on user unjoin (test was failing and email not sent)
* [helpdesk] handle some error cases
* [booking] show old client account used for a booking, in booking edition in case user switched to new client

## 2.7.0

### Features / enhancements

* [antibodies] use bootstrap modals and fix some menu urls
* [booking] add caldav support
* [services] feature kanban for project management
* [services] new gantt interface for project management
* [core] upgrade vue2 -> vue3
* [booking] calendar display updates for supplementaries, left align on detailed view (minor)
* [core] use ckeditor5 and allow to use multiple textareas with editor
* [invoices] get invoice by id (no longer by number) for generating pdf
* [com] allow private and public messages on welcome space message
* [invoices] for global invoices, in invoice details, show order identification number
* [services][sql] speed up orders list

### Fixes

* [services][orders] fix import of old pfm version for orders,
  fix order reopen on invoice deletion,

### Setup

* add support for env variable PFM_MEMORY which overrides default PHP memory limit for pfm-x processes,
  example: PFM_MEMORY: 1024M

## 2.6.4

* [core][cli] fix base columns settings (issue for old upgrades)

## 2.6.3

* [booking][invoice] fix invoice details rounded values #659

## 2.6.2

* [booking] do not show in welcome page delete reservations #655
* [booking] allow users to edit not started resa if bookingdelayusercanedit = -1 (booking restrictions) #654
* [booking] fix phone display in summary view #653
* [booking][core] in welcome page, fix bookings hours (gmt)
* [booking] on mandatory booking supplementaries, check also on server side

## 2.6.1

* [core] fix space creation and unimpersonate action #650
* [core] in visitor mode, add warning to user to login to access all features
* [core] disable by default tls for smtp, allow to activate via config smtp_tls

## 2.6.0
### Warning

Now, quote module manage its own template for pdf generation. Existing template is copied from invoices if exists at install time. If no template is available, a default example will be used.

A warning is deplayed to inform you if no template is defined.

Quote templates are configured the same way than invoice templates.

### Features / enhancements

* [cli] add config option to display configuration
* [core] add login button on welcome page
* [booking] in booking invoice details, show related bookings
* [booking_settings] add getDefault to BkNightWE class
* [core] remove lasting references to ec classes
* [core] add meta block to block robots indexing for some pages
* [core] on user join request, request a comment from user to be added to email
* [booking] add to scheduling option to force usage of packages to an area
* [booking] hide nightwe entries if pricing does not exists anymore
* [mail] add support for custom headers
* [booking] weekarea calendar, use summary view by default
* [core] catch email errors and log them
* [quote] separate invoice and quote templates, each can define his own template for pdf generation #548

### Bug fixes

* [bulletjournal] fixes on creation and edit
* [cli] add config option to display configuration
* [booking][supplementaries] avoid having two sups with the same name for the same resource
* [booking][supplementaries] allow to delete duplicates or refering to a deleted resource
* [booking][supplementaries] can't add a resource to a sup in which it is already included
* [helpdesk] do not send ticket reply if mail coming from helpdesk
* [core] remove lasting references to ec classes
* [booking] fix closed days hidding in summary view
* [invoice][booking] manage case where total hours = 0
* [booking] for multi slot bookings, do not display next slot content
* [core] fix corespaceuseraccess when clients or booking modules are in inactive status
* [antibodies] fix tissus image column width #643
* [resources] fix resps cleanup, missing param

## 2.5.4

* [core][antibodies] fix acowner table definition and fix delete javascript function #638

## 2.5.3

* [invoice][services] fix services invoices (invoice id not set) #637

## 2.5.2

* [services] user is no longer mandatory in project and order forms

## 2.5.1

* [space] fix pending accounts bug
* [booking] on calendar entry display, allow no customer/no pricing (catch errors on computeDuration)
* [core][booking] layout fixes on booking edition (periodic)
* [booking] compute duration with 2 digits per minute
* [booking] weekarea calendar, use summary view by default
* [booking] if multiple prices are in db, pick first

## 2.5.0

* [core] fix tile display error if no space
* [core] hide space unsubscribe button if user is pending or not member of space
* [core] add redirects to TODO after doing todo actions
* [core] use bootstrap5 and update/remove some libraries
* [core] show number of user clients in user admin view
* [booking] add to calendar a summary view option
* [core][ldap] do not automatically add ldap users to spaces
* [core] add option to tables to view all elements
* [booking] list blocked resources and add a reason column on bkcalentry (booking, holiday, maintenance)
* [booking] change booking compute (day/night/we/closed) and get details #565
* [booking] fix package invoicing #606
* [core] in core user edit, show user spaces and pending spaces #607
* [statistics] generate stats in background (async) and save generated files for later download #551
* [booking] manage shared area calendar option to conflict resources on same calendar #578
* [core] space settings, add option to either remove user from space or set as inactive on expiration
* [helpdesk] skip emails if helpdesk not activated, else only skip auto-replies

## 2.4.1

* [core] add missing tableName properties in model constructors
* [space] improve todolist sql requests
* [helpdesk] close connection in anycase
* [core] fix TODO in corespaceadmin, do not get all bookings, just count them
* [core] if module is inactive, fix authorization (isUserMenuSpaceAuthorized)
* [core] disable timezone, impacts existing bookings recorded as UTC
* [booking] fix calendar when area has no resource
* [booking] fix calendar display when resa spans multiple days
* [booking] fix calendar month display for month overlap resa

## 2.4.0

### Features

* mailer:
  * record and display sent messages
  * module *can* be set at *user* level, space members will see
    messages previously sent, only users with *edit* right
    (module configuration) can send emails
* documents:
  * new file hierarchy document display (virtual directories)
  * in premium plans, possibility to set document visibility
    (public, members, private)
  * module *can* be set at *user* level, users will only see
    public or members level documents, users with *edit* right
    (module configuration) can create/edit documents.
  * booking: in booking configuration, calendar has a new option
    for labels display (*managers only*). If selected, only space
    managers will see the label (user id, etc.) in calendar.
  * invoices: invoices are now generated in background, avoiding
    a blank (and possibly long) blank page during generation.
    Invoices page will show generation status and errors if any.

## Security

* [antibodies] fix control access to module pages

### Updates

* [core] add space configuration helpers (interactive Todo)
* [mailer] allow access to space users, users can see sent emails
* [documentation] add packages documentation and use case
* [core] reduce space users options after a module is deactivated
* [documentation] update space join use case
* [documents] add visibility controls on documents to have public, private, user/client scopes
* [core] group users space access options on a single page
* [core] in corespaceaccess show convention download button only if present
* [invoices] show message if no template defined
* [core] add status and msg to core files
* [helpdesk] on file upload (manual), create dir if not exists
* [core] fix default sort order in tables and remove download button if no url
* [core] fix typos/spelling
* [booking] on calendar, showuser infos (name, phone,...) options: Hidden/Visible/Managers only Closes #509
* [booking] manage redirect after reservation edit to page were booking was done
* [booking] fix default color code
* [users] if date_end_contract is empty, insert null in db
* [core] code cleanup #554
* [booking] handle booking settings fields *booking scale* and *user specifies*
* [booking] fix package display on new reservation
* [invoice] generate invoices in background (async) and show generation status
* [core] update dependencies (CVE on twig/twig, update guzzle and influxdb client)
* [booking] graphics and code refactoring of calendar
* [core] handle multiple Accept values in http headers for API calls (application/json)
* [catalog] fix default config display and layout
* [antobodies] fix image_url settings in tissues creation
* [antibodies] fix ACL checks
* [core] add reset for user api key

## 2.3.3 [unreleased]

* [booking] fix display of package on new booking Closes #563

## 2.3.2

* [booking][services] fix popup when editing with pagination #553
* [services] fix date on followup when in french #555
* [invoices] fix template var name clientsInfos -> clientInfos
* [booking] fix resource status color in dayarea/weekarea
* [booking] fix missing import for invoices

## 2.3.1

* [core] fix upgrade for redis invoice numbers (1641387865_update_invoice_numbers_redis.php)
  If already applied with 2.3.0, need to remove it from pfm_upgrades table and re-apply install

## 2.3.0

* [invoices] fix non-numeric quantity and/or price cases in invoice edition
* [services] dynamize client selection in project edition
* [services] services quantity types differenciation in projects, orders and invoices
* [coreconfig] remove module color edition from core config
* [booking] fix all_day reservation end date
* [core] remove most references to CoreTranslator::dateFromEn()
* [spaces] remove request access button from private spaces
* [booking] clicking on blank days in month view gives access to day area view
* [projects] fix closed projects listing change of year
* [resources] fix re_visa is_active default value
* [core] add possibility for a space manager to add an existing account #479
* [core] allow space plan modification #429
* [invoice] get invoice number per space, and not global
* [core] fix prometheus redis port
* [coreconfig] fix maintenance mode authorizations
* [coreconfig] remove carousel
* [invoices] remove hidden characters in template
* [docker] fix mysql db name in docker-compose
* [booking] add journal page to show future and last 30 days bookings
* [core] manage http error codes #184
* [layout] display enhancements
* [core] add index on id_space
* [services] fix services orders errors with client not registered
* [core] set user deactivation settings per space
* [booking] check booking start/end time and day at reservation
* [core] add button to user spaces
* [statistics] various fixes
* [antibodies] fix missing table creation at install
* [booking] send emails only if user status > VISITOR in space
* [com] check ACLs on com module and limit edition to space admins
* [booking] fix booking emails if invalid/recuring, notify if new or update

## 2.2.2

* [booking] fix scheduling creation for new resource areas
* [db] add upgrade_v3_v4 migration script on se_order
  **Warning**: if you installed release <= 2.2.2 you need to run a db fix script
  php cli/pfm-cli.php repair --bug 499
* [services] fix orders blocking bugs
* [invoices] fix orders invoice
* [core] fix prometheus redis port
* [invoices] remove hidden characters from template
* [docker] fix db name in compose #495

## 2.2.1

* [login] clarify messages toward users
* [booking] fix authorizations when related to user's roles
* [helpdesk] sort tickets and fix refresh
* [helpdesk] ignore auto replies
* [core] check id_user to see if user is logged
* [core] on register after email validation validate the account #472
* [statistics] require env var MYSQL_ADMIN_PWD: ${MYSQL_ROOT_PASSWORD} in pfm/pfm-events containers
* [core] use module url and not name for notifications #476
* [cli][core] update way to expire users, and on delete just anon user
* [clients] on user add , fix flash message display #477

## 2.2.0

**Warning**: [admin] Ldap configuration modification from ini or env variables
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
