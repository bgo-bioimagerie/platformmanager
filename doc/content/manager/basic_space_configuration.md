---
title: "Basic space configuration"
date: 2021-06-23T16:08:15+02:00
weight: 1
---

For basic booking fonctions, you will have to activate and configure *Clients module*, *Resources module* and *Booking module*.

## Modules activation

To activate a new module, go to *Configuration module*.

![basic configuration](../../basic_configuration_1.png)

Then, click the *Edit* button of the corresponding module.

![basic configuration](../../basic_configuration_2.png)

You have to set the *Users selector* to the lower role you want to grant access to this module.
(*i.e.* if *user* is set, then, users, managers and admins will be granted access).
Validate the activation by clicking *save* button.

This is the basic case. Some modules have more options. For more details, see [modules](../../modules/).

When activating *Booking module*, don't forget to activate *Booking settings module* (same screen), which will be needed further.

To simplify this first configuration, we'll set *Use authorisation visa* to "no". If you want to use *visas*, you will be able to come back here later.

Into a module configuration (*i.e.* Booking configuration in this example), you have to save each group of options you want to change (*e.g* "Activate/deactivate menus" **and** "Use authorisation visa" here).

![basic configuration](../../basic_configuration_4.png)

After having activated these 4 modules (including Booking settings), your space home page should look like this:

![basic configuration](../../basic_configuration_3.png)

## Create some resources
See [Resources](../../modules/module/resources/#create-some-resources)

You now have to identify your platform resources, their areas and categories, and create it in *Resources module*.

## Create a Client
See [Clients](../../modules/module/clients/#create-a-client)

To book a resource, In most cases, a *user* must be linked to a *client*. The client is the entity you send invoices to.

[comment]: # (A client can have multiple users, and a user can be linked to multiple clients)

## Link users to a client
See [Clients](../../modules/module/clients/#link-users-to-a-client)

One last step and you'll be ready to make your first booking.

## Authorize user to book a resource

By default, users are not authorized to book resources. To make a user able to book a resource, you have to go into *users module*.
Note that, since you have activated the *Booking module* a *Booking access* button is now displayed aside the users names.

See [Users module](../../modules/users/#manage-users-booking-access)

## Configure your Booking module

## Add color codes

Go to *Booking settings* and add a color code by clicking the '+' icon next to *Color codes* in the left side menu.

It is required in order to generate the booking view.

![basic configuration](../../booking_settings_module_1.png)

Now users should be able to book resources!
