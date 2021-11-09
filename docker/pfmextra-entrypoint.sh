#!/bin/bash
set -e

/setup.sh

/wait

cd /var/www/platformmanager
php ./bin/pfm-$1.php
