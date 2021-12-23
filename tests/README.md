# how to

Database must be up and running and Config/conf_test.ini adapted to db config

    DEBUG=1 PFM_CONFIG=Config/conf_test.ini PFM_MODE=test ./vendor/phpunit/phpunit/phpunit --stderr

Credits:

* waitfor-it: https://github.com/vishnubob/wait-for-it/blob/master/wait-for-it.sh, MIT license
