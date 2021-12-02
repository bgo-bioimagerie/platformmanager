#!/bin/bash
set -e

cp /etc/platformmanager/conf.ini.sample /var/www/platformmanager/Config/conf.ini
sed -i "s/MYSQL_URL/${MYSQL_HOST}/g" /var/www/platformmanager/Config/conf.ini
sed -i "s/MYSQL_DBNAME/${MYSQL_DBNAME}/g" /var/www/platformmanager/Config/conf.ini
sed -i "s/MYSQL_USER/${MYSQL_USER}/g" /var/www/platformmanager/Config/conf.ini
sed -i "s/MYSQL_PASS/${MYSQL_PASS}/g" /var/www/platformmanager/Config/conf.ini

if [ "a${PFM_MODE}" = "adev" ]; then
   sed -i "s/ini_set('display_errors', 0);/ini_set('display_errors', 1);/g" /var/www/platformmanager/index.php
   sed -i "s/error_reporting(0)/error_reporting(E_ALL)/g" /var/www/platformmanager/index.php
fi

# make sure this is not accessible from the webapp (no risk of leak)
unset MYSQL_USER
unset MYSQL_PASS
