---
title: "Requirements"
date: 2021-06-23T15:52:39+02:00
weight: 1
---
## Install from source

* Database: mysql > 5.6, tested on 8
* PHP:
  * version >= 8
  * with gd (jpeg, xpm, freetype support), pdo, pdo_mysql, mysqli, zip, ldap, imap and sockets support
* Apache server with:
  * PHP enabled
  * rewrite, headers, proxy_http modules
  * use of .htaccess

Apache should also have specific pfm configuration:

    <Location /update>
      Order deny,allow
      Deny from all
      Allow from 127.0.0.1
    </Location>
    
    <Directory "^${docroot}/data">
      <FilesMatch ".+\.*$">
        SetHandler !
      </FilesMatch>
    </Directory>

* Rabbitmq server for additional services (pfm-events, etc.)
* Influxdb server for statistics (optional)
* Redis for prometheus statistics support (optional for prometheus, mandatory for module antibodies)

## Install with docker

You just need docker and docker-compose ;-)
