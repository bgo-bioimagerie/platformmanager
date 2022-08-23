---
title: "Invoices"
date: 2022-08-23T13:30:30+02:00
---


**Invoices module** allows you to create and send invoices related bookings, orders and projects.

![invoices_module](../../../invoices_module_5.png)

Within this module, you can:
- [*generate booking invoices*](./#booking-invoices)
- [*generate project invoices*](./#project-invoices)
- [*generate order invoices*](./#order-invoices)
- [*generate global invoices*](./#global-invoices)
- [*define prices*](./#defining-prices)

## Module activation and configuration

Within your space, go into _Configuration_ module.

![basic configuration](../../../basic_configuration_1.png)

Then click edit button in _invoices_ line.

You can now select the minimum role required to access the module. "Inactive" option stands for "the module is not active".

![invoices_configuration](../../../invoices_module_0.png)

Don't forget to save your choice.

### HTML - PDF Template

By default, pdf invoices are generated using an integrated template. You can also upload a template of yours if needed. Templating langage used is Twig.

## Generating invoices

General informations:

- when used, the period concerns reservations start dates: only reservations *started* between begin period (included) and perdiod end (excluded) will be considered.

- after having requested invoice generation by clicking *save*, you will be redirected to the *To send invoices* page where your invoice should be listed.
- In some cases (especially if you requested invoice for all clients for an entire year), the invoice generation can take time.
In this case, the list of invoices in generation process is displayed at the top of the page. You can click *refresh* button to refresh theirs generation state information.

- You can then check if an invoice informations are correct by clicking on its *Edit* button.

- For more information on invoices edition, see [Editing invoices](./#editing-invoices).

### Global invoices

Generates a global invoice (orders + projects + bookings) per client.

The period has to be defined. You can either choose to selct a unique client.

![invoices_module](../../../invoices_module_7.png)

The content of a global invoice mixes services - from projects and orders - and booked resources:

![invoices_module](../../../invoices_module_8.png)

### Booking invoices

Under the *Services* section in the left side menu, go into *New invoice*.

You can either invoice all clients or choose a unique client to invoice.

In both cases, you'll have to define the targeted period.

![invoices_module](../../../invoices_module_1.png)

### Project invoices

Under the *SERVICES* section in the left side menu, go into *Invoice projects*.

You can either invoice all projects for a client and a period or choose a unique project to invoice.

![invoices_module](../../../invoices_module_3.png)

### Order invoices

Under the *SERVICES* section in the left side menu, go into *Invoice orders*.

You can invoice all orders for a client and a period.

![invoices_module](../../../invoices_module_4.png)

## Editing invoices

![invoices_module](../../../invoices_module_5.png)

After having generated an invoice, it is possible to edit it, to see its details and to generate a pdf. For that, go into *To send invoices*, at the top of the left side menu.

In some cases (especially if you requested invoice for all clients for an entire year), the invoice generation can take time.
In this case, the list of invoices in generation process is displayed at the top of the page. You can click *refresh* button to refresh theirs generation state information.

![invoices_module](../../../invoices_module_2.png)

## Defining prices

By default, prices are set to 0€.

You can edit them in *Prices* pages, under *BOOKING* (for resources prices) or *SERVICES* (for services prices) sections.

![invoices_module](../../../invoices_module_6.png)

For both resources and services, different prices can be defined according to the [client's pricing](../../module/clients/#pricing).
