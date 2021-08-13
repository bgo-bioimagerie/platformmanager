---
title: "Welcome"
date: 2021-06-23T15:44:36+02:00
---

## About

Platform-Manager is a research facility management system. 

It has been designed to facilitate the management 
of research facilities. It integrates tools for managing users 
(membership, responsible, convention...), tools for managing and 
booking resources (rooms, microscopes ...), service management 
and business databases. It allows to automatically generate 
quotes & invoices for each resource or services according 
to the reservations or the orders.

Platform-Manager is built in a modular way allowing the integration 
of additional functionalities with the core functionalities. 
For example, modules for stock management, antibody management, 
file sharing, downloading of data from resources have been developed.

## Use and Extend

Platform-Manager is a free and open-source tool (GPL). Add your own modules to match your needs.

Code: [https://github.com/bgo-bioimagerie/platformmanager](https://github.com/bgo-bioimagerie/platformmanager).

## How it works?

Platform-Manager is built in 2 levels: a global level of
administration and a level consisting of spaces.

* **Administration** : The administration module allows the
  system administrator to configure and update Platform-Manager.
  This module also contains the configuration of Platform-Manager:
  installation and configuration of spaces, logo, homepage content ...
* **Spaces** : Platform-Manager consists of independent "spaces".
  Each space is a public or private domain in which modules are enabled.
  Thus, a set of facilities can share a single instance of
  Platform-Manager with each their dedicated space. The tools
  available in the "spaces" come from the modules, some of which
  are interdependent. Refer to the [modules]("modules/")
  documentation.

## Usage

* [administrators](./admin)
* [space managers](./manager)
* [users](./user)
