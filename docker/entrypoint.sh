#!/bin/bash
set -e

service atd start

if [ ! -e /var/www/platformmanager/data/core ]; then
    cp -r /opt/data/* /var/www/platformmanager/data/
fi

/setup.sh

chown -R www-data:www-data /var/www/platformmanager/data

# Run the database update script in a few seconds
#echo "sleep 10; curl http://localhost/caches > /var/log/startup_db_caches.log; curl http://localhost/update > /var/log/startup_db_update.log" | at now
#echo "sleep 10; cd /var/www/platformmanger && php cli/pfm-cli.php --install > /var/log/startup_db_update.log" | at now

/wait

cd /var/www/platformmanager && php cli/pfm-cli.php routes --reload
exec apache2-foreground
