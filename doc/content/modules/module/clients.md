---
title: "Clients"
date: 2021-08-13T13:30:30+02:00
---

**Clients module** allows you to create invoicable entities.

![clients_module](../../../clients_module_8.png)

Within this module, you can:
- [manage *clients*](./#add-a-client)
- [manage *pricings*](./#add-a-pricing)
- [link *users* to a *client*](./#link-users-to-a-client)

## Item definition

A **client** is the entity which can be invoiced. Users can be attached to a client, so they can book resources or ask for services in name of the *client*. A user can't be invoiced directly.

A *client* is defined by its ("\*" for *mandatory*):
- Identifier*
- Contact name
- Phone
- Email*
- [Pricing*](./#pricing)
- Invoice send preference

## Related items definition

### Pricing

- a **pricing** is used to define a specific group of prices for resources and services tarification. In *Invoices module*, you can define prices for each resource and each service relativly to each pricing. 


## Module activation and configuration

Within your space, go into *Configuration* module.

![basic configuration](../../../basic_configuration_1.png)

Then click edit button in _clients_ line.

You can now select the minimum role required to access the module. *Inactive* option stands for *the module is not active*.

![clients_configuration](../../../clients_module_0.png)

Don't forget to save your choice.

## Create a client

Let's go into the *Clients module*.
Don't rush it, we must have created at least one *pricing* before being able to create our first *client*.

[comment]: # (A client can have multiple users, and a user can be linked to multiple clients)

![basic configuration](../../../clients_module_1.png)

### Add a pricing

To add a pricing, just click on *Pricings* in the left side menu. Then click *New pricing* button.

![basic configuration](../../../clients_module_2.png)

Then you can edit your *pricing*.

![basic configuration](../../../clients_module_3.png)


### Add a client

Now you can add your first client.
To do that, just click on *Clients* in the left side menu. Then click *New client* button.

![basic configuration](../../../clients_module_4.png)

Then you can edit your *client*.

![basic configuration](../../../clients_module_5.png)

Once validated, you'll also have to fill Address invoice and Address delivery forms.

### Link users to a client

You can link users to a client by clicking the *Users* button for your client.

![basic configuration](../../../clients_module_6.png)

Then, just choose a user amongst the User selector.

![basic configuration](../../../clients_module_7.png)
