---
title: "Docker"
date: 2021-06-23T15:51:32+02:00
weight: 3
---

## Using docker

### setup

Docker images are available at quay.io/bgo_bioimagerie,
see [docker-compose.yml](https://github.com/bgo-bioimagerie/docker-platformmanager/blob/master/docker-compose.yml) as compose example.

Update docker-compose.yml file environment sections with your setup.

Some variables are defined in a *.env* file for sensitive data.

Example:

    PFM_WEB_URL=http://localhost:4000
    PFM_ADMIN=pfmadmin
    # Warning, those are secrets!!!
    # min 8 characters
    PFM_ADMIN_PASSWORD=mysensitivesuperadminpassword
    PFM_ADMIN_EMAIL=admin@pfm.org
    PFM_ADMIN_APIKEY=123456
    PFM_INFLUXDB_TOKEN=123456
    MYSQL_ROOT_PASSWORD=xxxx
    MYSQL_PASSWORD=xxxx

Exemple docker-compose.yml use local docker volumes to save database etc.
Only local host mounted volume is *data* directory which contains space upload files,
which should be writable by container *www-data* user and shared among pfm
instances.

Though most data are in docker volumes, data *must* be backuped to an external system
(databases).

### Config/Env variables

Following env variables can be used to override Config/conf.ini:

* DEBUG: 0  # activate debug log level
* DEBUG_SQL: 1  # activate sql log level (not for production)
* MYSQL_HOST: mysql # mysql server name
  * MYSQL_DBNAME: platform_manager # name of the database on the mysql server
  * MYSQL_USER: platform_manager # Admin account to connect to mysql
  * MYSQL_PASS: platform_manager # Password to connect to mysql
* SMTP_HOST: mailhog  # smtp host name
* SMTP_PORT: 25  # smtp port
* MAIL_FROM: support@genouest.org  # mail *from* address
* PFM_MODE: prod  # optional [dev|*prod*|test], dev mode adds a console in browser with sql info
* PFM_ADMIN_USER: pfmadmin  # superadmin user name (automatically created)
* PFM_ADMIN_EMAIL: admin@pfm.org  # superadmin email
* PFM_ADMIN_PASSWORD: ${PFM_ADMIN_PASSWORD}  # superadmin password
* PFM_ADMIN_APIKEY: ${PFM_ADMIN_APIKEY}  # superadmin apikey, ifnot set, will be generated at account creation
* PFM_HEADLESS: 0|1 # optional headless mode (navbar) , default 0
* PFM_ROOTWEB:  # optional, default / to serve app with prefix
* PFM_PUBLIC_URL: ${PFM_WEB_URL}  # public http address for pfm service
* PFM_AMQP_HOST: pfm-rabbitmq  # host for rabbitmq
  * PFM_AMQP_USER: pfm  # rabbitmq user
  * PFM_AMQP_PASSWORD: pfm  # rabbitmq password
* PFM_OPENID: ${PFM_OPENID}  # comma separated list of external openid providers (google, orcid)
  * PFM_OPENID_GOOGLE_ICON: /externals/auth/btn_google_signin_dark_normal_web.png
  * PFM_OPENID_GOOGLE_URL: ${PFM_OPENID_GOOGLE_URL}
  * PFM_OPENID_GOOGLE_LOGIN: ${PFM_OPENID_GOOGLE_LOGIN}
  * PFM_OPENID_GOOGLE_CLIENT_ID: ${PFM_OPENID_GOOGLE_CLIENT_ID}
  * PFM_OPENID_GOOGLE_CLIENT_SECRET: ${PFM_OPENID_GOOGLE_CLIENT_SECRET}
* PFM_INFLUXDB_URL: http://influxdb:8086  # influxdb url
  * PFM_INFLUXDB_TOKEN: ${PFM_INFLUXDB_TOKEN}  # influxdb access token
  * PFM_INFLUXDB_ORG: pfm  # influxdb default organization
* PFM_ALLOW_REGISTRATION: 0  # (dis)allow user self registration
* PFM_JWT_SECRET: XXXX  # JWT tokens secret
* PFM_MODULES:   # comma separated list of modules to load (in addition to those defined in conf.ini)
* PFM_REDIS_HOST: redis # optional, redis host name, needed for prometheus stats
* PFM_GRAFANA_URL: http://grafana:3000  # optional, grafana url
  * PFM_GRAFANA_USER: admin
  * PFM_GRAFANA_PASSWORD: ${PFM_ADMIN_PASSWORD}
* PFM_SENTRY_DSN: # optional, catch errors and send to an external Sentry server

And .env file should define (according to variables used):

* PFM_WEB_URL=http://localhost:4000
* PFM_ADMIN=pfmadmin
* PFM_ADMIN_PASSWORD=admin4genouest  # min 8 characters
* PFM_ADMIN_EMAIL=admin@pfm.org
* PFM_ADMIN_APIKEY=123456
* PFM_INFLUXDB_TOKEN=123456
* PFM_OPENID=  # comma separated list of supported providers
* If PFM_OPENID is defined:
  * PFM_OPENID_GOOGLE_URL=https://oauth2.googleapis.com/token
  * PFM_OPENID_GOOGLE_LOGIN=https://accounts.google.com/o/oauth2/v2/auth
  * PFM_OPENID_GOOGLE_CLIENT_ID=XXX
  * PFM_OPENID_GOOGLE_CLIENT_SECRET=XXX
* MYSQL_ROOT_PASSWORD=XXX
* MYSQL_PASSWORD=XXX
