---
title: "Quote"
date: 2021-08-13T13:30:30+02:00
---

**Quote module** allows you to create quotes.

![clients_module](../../../quote_module_1.png)

Within this module, you can:

- [Create *quotes*](./#create-a-quote)
- [Edit *quotes*](./#edit-quote)

## Item definition

A *quote* is defined by its ("\*" for *mandatory*):

- [Recipient or User*](./#recipient)
- Recipient Email
- Client Address
- [Client*](./#client)
- A list of *items*

## Related items definition

### Recipient or User

The **recipient** or [**user**](../users) is the person whose name will appear in the quote header.

*Recipient* is set in case of a [*new user quote creation*](./#create-a-new-user-quote).

*User* is set in case of a [*new quote creation*](./#create-a-new-quote).

### Client

The **client** is the structure whose name and address will appear in the quote header right under the recipent's or user's name.
see [Clients](../clients)

### Item

**Items** are the invoicable elements to which quote relates. They can be [services](../services) or bookable [resources](../resources).

## Module activation and configuration

Within your space, go into *Configuration* module.

![basic configuration](../../../basic_configuration_1.png)

Then click edit button in *quote* line.

You can now select the minimum role required to access the module. *Inactive* option stands for *the module is not active*.

![quote_configuration](../../../quote_module_0.png)

Don't forget to save your choice.

## Create a quote

Let's go into the *Quote module*.

Don't rush it, we must have created at least one [*client*](../clients/#add-a-client) before being able to create our first *quote*.

Any *client* to who you want to address a quote must also have been attributed a [*pricing*](../clients/#add-a-pricing).

In newest versions of Platform-Manager, a client is necesseraly linked to a pricing.

There are two ways to create quotes: [*Create a new quote*](./#create-a-new-quote) or [*Create a new user quote*](./#create-a-new-user-quote)

### Create a new quote

If you want to address your quote to an existing user.

Just click on *Create new quote* in the left side menu.

![quote_configuration](../../../quote_module_2.png)

Then you can set a *user* for this quote then choose a *client* amongst those linked to the selected *user*.

Click *save*. You will be redirected to the [Edit quote screen](./#edit-quote), where you can add items to your quote.

### Create a new user quote

If the person you want to address your quote to has no user account and you don't want to create one.

There is still need for an existing client.

Just click on *Create new user quote* in the left side menu.

![quote_configuration](../../../quote_module_6.png)

Then you can set a [*recipient*](./#recipient-or-user) for this quote, its email, and select a *client* amongst all client accounts referenced in your platform space.

Pricing is set automatically, accordingly to the selected client's.

Click *save*. You will be redirected to the [Edit quote screen](./#edit-quote), where you can add items to your quote.

## Edit quote

You can here add [*items*](./#add-an-item) to your quote, change user and/or client.

If this quote was created in the [*Create new user quote*](./#create-a-new-user-quote) form, you can change its [*recipient*](./#recipient-or-user) and *recipient email* instead of its *user*.

![quote_configuration](../../../quote_module_7.png)

Pricing is set automatically, accordingly to the selected client's.

### Add an item

Click on the *New item button*. A popup window displays.

![quote_configuration](../../../quote_module_4.png)

Then select a service or a bookable resource in *Service / resource* list, add a quantity and, eventually a comment.

this will add an item line to your quote.

![quote_configuration](../../../quote_module_5.png)

## Generate a PDF file

Once your quote is set up, you can generate a pdf file from it by clicking the *PDF* button.

This file will be generated accordingly to the template you set in your [*invoices*](../invoices) module configuration.
