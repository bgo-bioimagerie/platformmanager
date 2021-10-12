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
Then click edit button for _resources_ line.
