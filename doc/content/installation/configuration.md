---
title: "Configuration"
date: 2021-06-23T16:05:10+02:00
weight: 5
---
## General configuration

Configuration file, located in Config/conf.ini defines base configuration
and the list of modules globally activated.

Example file is available with conf.ini.sample.

Mandatory sections are:

* Installation: base setup with public url access and optional url prefix
* Modules: list of modules
* DB: mysql connection information
* Admin: super administrator information (created at install if not exists),
  admin secrets (password, apikey) *should* not be exposed in config file and
  should rather be defined via env variables (see below).

Optional sections:

* Amqp: rabbitmq connection information, used for events and statistics
  to dialog with pfm-events process
* Influxdb: Influxdb connection information
* Openid: external authentication systems credentials

## LDAP configuration

If users are to be authenticated via LDAP, ldap settings must be set in Config/conf.ini or
env variables.
Confgiguration via a Config/ldap.ini is file is still supported for backward compatibility
(see ldap.ini.sample). A single LDAP system is used for all spaces access
in this case, though local users can still be used in parallel.

## Environment variables

Most configuration file variables can be superseeded by environment
variables, usually in format **PFM_VARNAME**, *PFM_ADMIN_USER* for example for *admin_user* file config equivalent.

See *docker* section.

## Welcome page

The default welcome page displays content of the first file found in *data* directory:

* welcome_[en|fr].md
* welcome_[en|fr].html*
* welcome.md
* welcome.html

Files in markdown (.md) are converted to HTML.
