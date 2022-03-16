---
title: "How to manage interdependant booking resources"
date: 2021-06-23T16:08:15+02:00
weight: 1
---

## Use case
- you have one equipment for which users need a specific authorization
- with this equipment, users can perform more than one activity
- eventually, these activities have different prices

## Problem
Using one [category](../../modules/module/resources/#category) (on which users authorizations can be set) representing the equipment and several [resources](../../modules/module/resources), each of them representing one activity linked to this equipment, do not avoid a user to make a booking for one of these activities whereas some other user already did book another activity for this equipment.
Consequently, two (or more) users can book the same equipment at the same time, resulting in schedule conflicts.


## Solution

Use [packages](../../modules/module/booking/#packages) !

- [create 1 category](../../modules/module/resources/#add-a-category) to represent your equipment (so you can set its booking authorisations)
- linked to this category, [create only 1 resource](../../modules/module/resources/#add-a-resource) avoiding the possibility of simultaneaous reservations for the same equipment
- from this resource, create as many packages as there are activities. (A package is described by its name, duration and resource it's based on

Create packages in booking settings > packages

![packages](../../packages_1.png)

Set packages prices in Invoices > booking > Prices

![packages](../../packages_2.png)

Packages are available when booking the attached resource

![packages](../../packages_3.png)

If selected in reservations, packages are used in booking invoices

![packages](../../packages_4.png)
