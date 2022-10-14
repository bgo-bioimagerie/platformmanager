---
title: "Source"
date: 2021-06-23T18:13:29+02:00
weight: 2
---

## Install from source code

### About

Code is hosted in git repository from [https://github.com/bgo-bioimagerie/platformmanager](https://github.com/bgo-bioimagerie/platformmanager).

Stable code is in *master* branch.

Releases can also be download from [https://github.com/bgo-bioimagerie/platformmanager/releases](https://github.com/bgo-bioimagerie/platformmanager/releases).

Code contains some .htaccess to limit access to specific directories.
If not using Apache as frontend, you need to restrict access
yourself based on .htaccess files definition.

In Config, define a *conf.ini* (see [Configuration](../configuration)) with
appropriate setup.

See [docker](../docker) for available env variables to
override defaults.

### Debian install

    # base setup, adapt php8.X-mysql to your distribution package (apt-cache search php | grep mysql)
    sudo apt-get install apache2 php php-redis php-imap php-gd php8.X-mysql php-zip php-ldap php-imap composer git
    sudo a2enmod rewrite
    sudo a2enmod headers
    sudo a2enmod proxy_http
    sudo systemctl restart apache2

In the following we suppose you install pfm in /var/www/html/platformmanager path

    cd /var/www/html
    git clone https://github.com/bgo-bioimagerie/platformmanager.git
    chown -R www-data platformmanager
    cd platformmanager
    # Install pfm dependencies
    sudo composer install

Copy from cloned repo to /etc/php/8.x/apache2/conf.d:

* docker/php_logs.ini to 99-pfmlogs.ini
* docker/php_timezone.ini to tz.ini to 99-pfmtz.ini
* docker/php_pfm.ini to 99-pfm.ini

Copy from cloned repo to /etc/apache2/conf-enabled and update Directory path to your install path

* docker/apache2/000-default.conf

Copy from cloned repo to /etc/apache2/sites-enabled and update ServerName/ServerAdmin/DocumentRoot
to match your settings (DocumentRoot would be here /var/www/html/platformmanager):

* docker/apache2/pfm.conf

    sudo service apache2 restart

Start pfm-events and pfm-helpdesk (optional) from bin directory (using systemd)

### Systemd

In /etc/systemd/system:

* Pfm-events

    [Unit]
    Description=pfm-events service

    [Service]
    User=www-data
    PIDFile=/var/run/pfm-events.pid
    WorkingDirectory=/var/www/html/platformmanager
    ExecStart=php /var/www/html/platformmanager/bin/pfm-events.php
    ExecReload=/bin/kill -s HUP $MAINPID
    ExecStop=/bin/kill -s TERM $MAINPID
    PrivateTmp=true

    [Install]
    WantedBy=multi-user.target

* Pfm-helpdesk

    [Unit]
    Description=pfm-events service

    [Service]
    User=www-data
    PIDFile=/var/run/pfm-events.pid
    WorkingDirectory=/var/www/html/platformmanager
    ExecStart=php /var/www/html/platformmanager/bin/pfm-helpdesk.php
    ExecReload=/bin/kill -s HUP $MAINPID
    ExecStop=/bin/kill -s TERM $MAINPID
    PrivateTmp=true

    [Install]
    WantedBy=multi-user.target

### Configuration

Edit and update Config/conf.ini from Config/conf.ini.example

### Install

See [install](../upgrade)
