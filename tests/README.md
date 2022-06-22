# how to

Database must be up and running and Config/conf_test.ini adapted to db config

    DEBUG=1 PFM_CONFIG=Config/conf_test.ini PFM_MODE=test ./vendor/phpunit/phpunit/phpunit --stderr

To skip install: INSTALL=0
For code coverage you need xdebug extension and XDEBUG_MODE=coverage and specify --whitelist directories (Framework, Modules)

    XDEBUG_MODE=coverage DEBUG=0 PFM_CONFIG=Config/conf_test.ini PFM_MODE=test ./vendor/phpunit/phpunit/phpunit --stderr --coverage-html /tmp/out --whitelist Framework --whitelist Modules

To also tests views add PFM_TEST_VIEW=1

Credits:

* waitfor-it: https://github.com/vishnubob/wait-for-it/blob/master/wait-for-it.sh, MIT license
