---
title: "Booking"
date: 2021-08-13T13:30:30+02:00
---

**Booking module** allows *users* book *resources*.

![booking_module](../../../booking_module_1.png)

Unlike other modules, Booking module has the particularity to be divided into two modules: [**Booking**](#booking-module) and [**Booking settings**](#booking-settings-module).
You will need to activate both in order set your resources reservation environment.

**Module descritpion**

## Item definition

A reservation is defined by its:

- [Resource](../resources/#item-definition)
- [User](../users/#item-definition)
- [Client account](../clients/#item-definition)
- Short description
- Full description
- [Color code](#color-code)
- (optional) [Additional infos](#additional-info)
-  All day, _i.e._ does this reservation takes all day?
- Beginning (date + time)
- End (date + time)
- (optional) Periodicity

## Related items definition

### Color code

### Additional info

#### Supplementaries info

For information on reservation. Can be set as mandatory.

#### Packages

    **To be documented**

#### Quantities

Allows to specify quantity of specific elements for a reservation. Can be set as mandatory.

If a quantity is set as *invoicing unit* for a resource, then its quantity will be taken into account for invoice generation instead of reservation duration.

Two quantities cannot be set as *invoicing units* for the same resource.

## Module activation and configuration

Within your space, go into *Configuration* module.

![basic configuration](../../../basic_configuration_1.png)

Then click edit button in *booking* line.

*Booking configuration* has several options:

- **Activate/deactivate menus**: select the minimum role required to access both the *Booking* and *Booking settings* modules. *Inactive* option stands for *the module is not active*.

    ![booking_configuration](../../../booking_module_config_1.png)

- **Use authorisation visa**: set if users have to have been attributed a visa to be able to book resources

- **Menu name**: change the menu name

- **Use recurrent booking**: allow to make periodic reservations

- **Can user edit started reservation?**: allow user to make changes to an existing reservation

- **Edit booking options**: choose description fields displaying when editing a reservation

    ![booking_configuration](../../../booking_module_config_2.png)

- **Edit reservation plugin**: **A documenter**

- **Edit booking mailing**: choose in which cases emails should be sent to users and / or space managers

- **Booking summary options**: choose which informations should display on reservations thumbails in calendar views

    ![booking_configuration](../../../booking_module_config_3.png)

## Booking settings module

![booking_settings](../../../booking_settings_module_0.png)

**Booking settings** module's role is to configure all booking-relative parameters, as the scheduling, resources reservation restrictions, and so on.

The default page displays only the side menu.

Following, the different items of *Booking settings*, divided into 3 categories (*Calendar View*, *Additional info* and *Booking*):

### Calendar View

*Calendar View* parameters define to what users have access.

#### Scheduling

**Warning**: in order to edit schedulings, you need to create [*color codes*](./#color-codes) first.

![booking_settings](../../../booking_settings_module_2.png)

Set, for each [*area*](../resources/#area):
- week days available for booking
- daily time slot available for booking
- booking blocs size (from 1/4 hour to 1 hour)
- default booking scale (minutes, hours or days)
- how do user specifies the booking duration
- default [*color code*](#color-codes)

![booking_settings](../../../booking_settings_module_3.png)

#### Display

![booking_settings](../../../booking_settings_module_4.png)

Set the colors and text options of the calendar view interface.

#### Accessibilities
**Warning** *for versions < 2.2*
Default booking authorizations show *User* by default, but are set to *Manager*

Need to be saved a first time to be taken into account.

Same each time you add a new resource, its authorizations are set to *Manager* even if it shows *User*.

![booking_settings](../../../booking_settings_module_5.png)

Set booking authorizations (*User*, *Authorized users list*, *Manager*, *Admin*) by resource for members of your space.

Options:
- **User**: every user is authorized to book this resource
- **Authorized users list**: only users with booking access for this resource's category are allowed to make a reservation (+ managers and admins)
- **Manager**: only managers and admins are allowed to make a reservation
- **Admin**: only admins are allowed to make a reservation

#### Restrictions

![booking_settings](../../../booking_settings_module_6.png)

Set max booking per day and cancellation time limit by resource.

#### Night & WE

This item has to deal with [*pricings*](../clients/#pricing).

![booking_settings](../../../booking_settings_module_7.png)

For each *princing*, apply different prices for day, night and weekend. Define when to apply this specific prices (day period and week-end period).

#### Color Codes

![booking_settings](../../../booking_settings_module_8.png)

Manage [*color codes*](#color-code)

### Additional infos

See [Additional info](#additional-info).

Set additional fields to the reservation form. Resource-relative.

![booking_settings](../../../booking_settings_module_9.png)



### Booking

#### Block resources

![booking_settings](../../../booking_settings_module_10.png)

Block resources so it can't be booked by users on this time period.

## Booking module

### Presentation

![booking_module](../../../booking_module_1.png)

*Booking module* view relies on 4 sections:

- **Main bar** allows to choose an *Area* or *Resource* to display (depending on the view chosen in *view selection bar*) and to set a date. 

![booking_module](../../../booking_module_3.png)

- **View selection bar** allows to navigate from day to day (or month or week) and to set a type of view (*Day*, *Day Area*, *Week*, *Week Area*, *Month*).

![booking_module](../../../booking_module_4.png)

- **Calendar section** displays booked resources depending on the *Area*, *Resource*, date and view set in *Main bar* and *View selection bar*. Clicking a "+" symbol gives access to reservation interface. Clicking a reservation thumbnail allows to modify the selected reservation.

![booking_module](../../../booking_module_5.png)

- **Color code section** records the available *color codes*.

![booking_module](../../../booking_module_6.png)

### Booking a resource

By clicking a "+" symbol, you can access the reservation interface.

![booking_module](../../../booking_module_2.png)

For elements description, see [item definition](#item-definition) and [related items definition](#related-items-definition)



