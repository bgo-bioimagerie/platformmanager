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
See [Resources](../../modules/module/resources)

You now have to identify your platform resources, their areas and categories, and create it in *Resources module*.

## Create a Client

To book a resource, In most cases, a *user* must be linked to a *client*. The client is the entity you send invoices to.
Let's go into the *Clients module*.
Don't rush it, we must have created at least one *pricing* before being able to create our first *client*.

[comment]: # (A client can have multiple users, and a user can be linked to multiple clients)

![basic configuration](../../clients_module_1.png)

### Add a pricing

To add a pricing, just click on *Pricings* in the left side menu. Then click *New pricing* button.

![basic configuration](../../clients_module_2.png)

Then you can edit your *pricing*.

![basic configuration](../../clients_module_3.png)

### Add a client

Now you can add your first client.
To do that, just click on *Clients* in the left side menu. Then click *New client* button.

![basic configuration](../../clients_module_4.png)

Then you can edit your *client*.

![basic configuration](../../clients_module_5.png)

Once validated, you'll also have to fill Address invoice and Address delivery forms.

### Link users to a client

Now that your first client is created, you can link users to it by clicking the *Users* button for your client.

![basic configuration](../../clients_module_6.png)

Then, just choose a user amongst the User selector.

![basic configuration](../../clients_module_7.png)

One last step and you'll be ready to make your first booking.

## Authorize user to book a resource

By default, users are not authorized to book resources. To make a user able to book a resource, you have to go into *users module*.
Note that, since you have activated the *Booking module* a *Booking access* button is now displayed aside the users names.

![basic configuration](../../users_module_1.png)

Click the *Booking access* button for the user you want to be able to book a resource.

![basic configuration](../../users_module_2.png)

Then click *Add* button (*i.e.* "add" this the user to the list of users who can book this resource) for the resource you want them to be able to book.

![basic configuration](../../users_module_3.png)

Fill *Activation date* and, eventually, *Visa* if you set your *Booking module* to require visas.

[comment]: # (When editing this doc, not filling visa returns an error)

## Configure your Booking module

## Add color codes

Go to *Booking settings* and add a color code by clicking the '+' icon next to *Color codes* in the left side menu.

It is required in order to generate the booking view.

![basic configuration](../../booking_settings_module_1.png)

Now users should be able to book resources!
