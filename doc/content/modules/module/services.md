---
title: "Services"
date: 2021-08-13T13:30:30+02:00
---


**Services module** allows you to create **projects**, **services** and **orders** as well as managing **stocks**.

![resources_module](../../../services_module_1.png)

Within this module, you can:
- [manage *projects*](./#add-a-resource)
- [manage *orders*](./#add-an-area)
- [manage *services*](./#add-a-category)
- [manage *stocks*](./#add-a-visa)
## Item definition

### Project

Provides project following using kanban board, gantt diagram.

Invoicable items - *services* - can be added to a project to have a track of what you'll have to invoice to your clients.

![resources_module](../../../services_module_3.png)

A **Project** is defined by its ("\*" for *mandatory*):
- identification number*
- [client account*](../../module/clients/#item-definition)
- [user*](../../module/users/#item-definition) (*i.e.* main user of the project)
- [Manager in charge (visa)*](./#add-a-visa)
- New team*
- New project*
- Origin*
- Time limit
- Opened date
- users (*i.e. other users related to the project. Main user will automatically be part of it)

### Order
### Service

A **Service** stands for an invoicable item to be related to a project or an order.
It is defined by its:
- name
- description
- type

*type* is to be chosen between *quantity*, *time in minutes*, *time in hours*, *half day*, *day* or *price*.

### Stock

// TODO: complete that
[comment]: # (TODO: Document that)


## Module activation and configuration

Within your space, go into _Configuration_ module.

![basic configuration](../../../basic_configuration_1.png)

Then click edit button in _resources_ line.

You can now select the minimum role required to access the module. "Inactive" option stands for "the module is not active".

![resources_configuration](../../../services_module_0.png)

Don't forget to save your choices: each type of item you want to use will have to be activated to enable it in the services module left side menu.

## Manage projects

### Creating a new project

First make sure to have created the *origin* and *visa* you want to use foe this project. It can be changed afterwards, but you won't be able to save your project creation if no visa or no origin is selected.

*Origin* and *visa* are accessible from the left side menu.

Then, you can go to *New project* and fill the form in.

![resources_module](../../../services_module_2.png)

### Creating a service

At the bottom of the left side menu, under *LISTING*, you can access the list of existing services by clicking on *Services* or add a new one by clicking on the *+* icon.

![resources_module](../../../services_module_4.png)

### Adding a service to your project

Go to your project. You can find it under *Opened*, *Period* or *Closed* projects, depending on its state. Then, click *Edit*.

Navigate to the the *Follow-up* tab then click on *New service*.

![resources_module](../../../services_module_5.png)

### Using the kanban Board

Kanban board is accessible from your project page under the *Kanban board* tab.

![resources_module](../../../services_module_6.png)

You can here create new tasks, new categories, and drag and drop each elements (except for the *done* category, which is always at the last position).

![resources_module](../../../services_module_7.png)

### Gantt diagram

Projects are also displayed in a gantt diagram.

![resources_module](../../../services_module_9.png)

By clicking on a project bar, you can access to the related tasks.

![resources_module](../../../services_module_8.png)

Task details are shown by clicking on its bar.

![resources_module](../../../services_module_10.png)

### Closing / invoicing projects

[comment]: # (TODO: make link toward invoice module documentation)

## Manage orders

### Creating a new order

Orders creation is accessible via the *New order* link, under *ORDERS* section in the left side menu.

![resources_module](../../../services_module_11.png)

### Adding a service to your order

Go to your order. You can find it under *Opened*, *Closed* or *All* orders, depending on its state. Then, click *Edit*.

If you already have created services, they will be selectable at the bottom of the form. If not, see [creating a service](./#creating-a-service)

### Closing / invoicing orders

[comment]: # (TODO: make link toward invoice module documentation)

## Manage stocks

[comment]: # (TODO: Document that)
