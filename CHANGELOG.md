# 2.0

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
