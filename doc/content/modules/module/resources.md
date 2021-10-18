---
title: "Resources"
date: 2021-08-13T13:30:30+02:00
---

**Resources module** allows you to create resources that users can book.

## Item definition

A *resource* is a bookable item.
It is defined by its ("*" for _mandatory_):
- name*
- [category*](#category)
- [area*](#area)
- brand
- type
- display order
- description
- full description
- image

## Relative items definition
<a name="category"></a>
- a **category** contain multiple resources. For example, if you have 3 scanners (_scanner1_, _scanner2_, _scanner3_), they can be grouped under the category _Scanners_.
<a name="area"></a>
- an **area** is a specific zone, or place. If your resources are shared between 2 labs (_lab1_, _lab2_), then your areas will be _lab1_ and _lab2_.


## Module activation and configuration

Within your space, go into _Configuration_ module.
![basic configuration](../../../basic_configuration_1.png)
Then click edit button in _resources_ line.

You can now select the minimum role required to access the module. "Inactive" option stands for "the module is not active".
![resources_configuration](../../../resources_module_0.png)
Don't forget to save your choice.

## Create some resources

Go into *Resources module*.

![basic configuration](../../../resources_module_1.png)

Before adding a resource, **you must** add at least one area and one category.

### Add a category

You can add a category by clicking the '+' icon next to *Categories* in the left side menu.

![basic configuration](../../../resources_module_2.png)

### Add an area

You can add an area by clicking the '+' icon next to *Areas* in the left side menu.

![basic configuration](../../../resources_module_3.png)

[comment]: # (TODO: Detail the meaning of restricted)

An area can be *restricted*.

### Add a resource

You can add a resource by clicking the '+' icon next to *Resources* in the left side menu.

![basic configuration](../../../resources_module_4.png)

The resource you created can now be accessed in booking module. A few more steps, and it will be available for booking !

### (option) Add a visa

In order to manage specific authorisations in resources booking, you can add *visas* to *resources categories*

You can add a visa by clicking the '+' icon next to *Visas* in the left side menu.

![basic configuration](../../../resources_module_5.png)