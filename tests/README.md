# how to

Database must be up and running and Config/conf_test.ini adapted to db config

    PFM_CONFIG=Config/conf_test.ini PFM_MODE=test ./vendor/phpunit/phpunit/phpunit --bootstrap tests/bootstrap.php tests/CoreTest.php
