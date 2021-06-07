# 2.0

* New command-line script cli/php-cli.php:
  * used for install commands

* New env variables:
  * DEBUG: activate logs with debug level
  * PFM_MODE: [prod(default)|dev|test] in test, activate browser dev tab,
    in mode test disable html rendering and return template vars on controller call.

* New config parameters:
  * sql_debug: combined with DEBUG mode, trace sql queries
