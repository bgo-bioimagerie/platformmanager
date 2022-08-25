---
title: "Resources"
date: 2021-08-13T13:30:30+02:00
---

[comment]: # (TODO: Document responsibles, states and events)

**Resources module** allows you to create resources that users can book.

![resources_module](../../../resources_module_6.png)

Within this module, you can:

- [manage *resources*](./#add-a-resource)
- [manage *areas*](./#add-an-area)
- [manage *categories*](./#add-a-category)
- manage responsibles and [visas](./#add-a-visa)
- manage resources states and events

## Item definition

A **resource** is a bookable item.
It is defined by its ("\*" for *mandatory*):

- name*
- [category*](./#category)
- [area*](./#area)
- brand
- type
- display order
- description
- full description
- image

## Related items definition

### Category

A **category** contains multiple resources. For example, if you have 3 scanners (_scanner1_, _scanner2_, _scanner3_), they can be grouped under the category _Scanners_.

### Area

An **area** is a specific zone, or place. If your resources are shared between 2 labs (_lab1_, _lab2_), then your areas are _lab1_ and _lab2_.

## Module activation and configuration

Within your space, go into *Configuration* module.

![basic configuration](../../../basic_configuration_1.png)

Then click edit button in *resources* line.

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

### Add a visa

In order to manage specific authorisations in resources booking, you can add *visas* to *resources categories*

You can add a visa by clicking the '+' icon next to *Visas* in the left side menu.

![basic configuration](../../../resources_module_5.png)

### Manage resources Accessibilities

See [booking settings accessibilities](../booking#accessibilities)
