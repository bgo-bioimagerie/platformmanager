#!/bin/bash
set -e

/setup.sh

/wait

if [ ! -z "$PFM_MEMORY" ]; then
	echo "Set PHP memory"
        sed -i '/memory_limit/c\memory_limit='$PFM_MEMORY'' /usr/local/etc/php/conf.d/pfm.ini
fi


cd /var/www/platformmanager
php ./bin/pfm-$1.php
