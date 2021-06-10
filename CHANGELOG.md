# 2.0

* New command-line script cli/php-cli.php:
  * used for install commands

* New env variables:
  * DEBUG: activate logs with debug level
  * DEBUG_SQL: activate sql logs when in debug level
  * PFM_MODE: [prod(default)|dev|test] in test, activate browser dev tab,
    in mode test disable html rendering and return template vars on controller call.
  * PFM_ADMIN_USER: default super admin id (default=admin)
  * PFM_ADMIN_EMAIL: default super admin contact email
  * PFM_ADMIN_PASSWORD: default super admin user password (install only, default=admin)
  * PFM_WEB_URL: web site public url (https://www.pfm.org)

* keycloak/internal ldap env variables:
  * KEYCLOAK_URL: http://keycloak:8080
  * KEYCLOAK_ADMIN_USER: kadmin
  * KEYCLOAK_ADMIN_PASSWORD: ${PFM_ADMIN_PASSWORD}
  * PFM_KEYCLOAK_OIC_SECRET: 54c58b91-7370-4a5c-aed5-27656e77bdfa
  * PFM_LDAP_HOST: openldap
  * PFM_LDAP_USER: cn=admin,dc=pfm,dc=org
  * PFM_LDAP_PASSWORD: pfm
  * PFM_LDAP_BASEDN: dc=pfm,dc=org
  * PFM_LDAP_BASESEARCH: ou=people,dc=pfm,dc=org


* New config parameters:
  * sql_debug: combined with DEBUG mode, trace sql queries
