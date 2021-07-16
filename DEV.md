# How to

## dependencies

need to install dependencies

composer install

## logs

index.php disable error logs by default, if needed, update log setup in index.php

## ldap

in Config, add ldap.ini

Example:

    ; Configuration for ldap
    ldapAdress = "192.168.1.99"
    ldapPort = "3890"
    ldapId = ""
    ldapPwd = ""
    ldapBaseDN = "ou=people,dc=pfm,dc=org"
    ldapUseTls = "FALSE"

# php imap support

install libs libc-client2007e-dev libkrb5-dev
docker-php-ext-configure imap --with-imap-ssl --with-kerberos
docker-php-ext-install imap

## Old code ecosystem

https://github.com/bgo-bioimagerie/platformmanager/blob/seek/Modules/ecosystem/EcosystemRouting.php

## database

switch to mysql:8, need to export and reimport data, upgrade is not possible
