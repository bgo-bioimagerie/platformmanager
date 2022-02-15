---
title: "How to manage interdependant resources booking configuration"
date: 2021-06-23T16:08:15+02:00
weight: 1
---

## Use case
- you have one equipment for which users need a specific authorization
- with this equipment, users can perform more than one activity
- eventually, these activities have different prices

## Problem
Using one category (on which users authorizations can be set) representing the equipment and several resources, each of them representing one activity linked to this equipment, do not avoid a user to make a booking for one of these activities whereas some other user already did book another activity for this equipment.
Consequently, two (or more) users can book the same equipment at the same time, resulting in schedule conflicts.


## Solution

Use packages !

- 1 equipment is described by 1 category (for which booking authorisations can be set)
- inside this category, only 1 resource is created (to block the schedule) avoiding the possibility of simultaneaous reservations for the same equipment
- from this resource, create as many packages as there are activities. (A package is described by its name, duration and resource it's based on. A package has its own prices TODO: create package entry in doc and make a link here)

Create packages in booking settings > packages

![join space](../../interdependant_resources_1.png)

Set packages prices in Invoices > booking > Prices

![join space](../../interdependant_resources_2.png)

Packages are available when booking the attached resource

![join space](../../interdependant_resources_3.png)

If selected in reservations, packages are used in booking invoices

![join space](../../interdependant_resources_4.png)
