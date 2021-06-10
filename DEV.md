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

