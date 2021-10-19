---
title: "Users"
date: 2021-08-13T13:30:30+02:00
---

**Users module** allows space admins to manage *users*.

![users_module](../../../create_user_9.png)

Within this module, you can:
- [add *users*](./#users-management)
- [authorize *users* to access your *space*](./#users-management)
- [affect roles to users within your *space*](./#users-management)
- [manage users *booking access*](./#manage-users-booking-access)
- [manage users *client accounts*](./#link-users-to-a-client)

## Item definition

A **user** is linked to an account. Within a space, it can be attributed different [*roles*](./#user-space-roles) , like *admin*, *manager*, *user* or *visitor*.

A *user* is defined by its ("\*" for *mandatory*):

- In Platform-Manager
    - Login*
    - Name*
    - Firstname*
    - Email*
    - Phone

- In a space
    - [Space role](./#user-space-role)
    - [Activity state](./#user-activity-state)

## Related items definition

### User space role

Within a *space*, a *user* as a *role*. The role is used to manage access to space modules and space administration.
- Only users with Admin role will have access to the space **Admin section** (*i.e.* *Space*, *Configuration*, *Users* and *History* modules).
- Each of the modules within the **Tools section** can be restricted to a certain level of user role in *Configuration module*.

### User activity state

Relatively to a space, a user can be *pending*, *active*, or *inactive*.

#### Pending
When a user account is linked to a space, his activity state is first set to *pending*. It means that they still have been affected no role for this space.

#### Active
A user is *active* when they have been affected a role for this space. He has access to *space modules* authorized for his role.

#### Inactive
A user is *inactive* when his role has been set to *inactive*. The user account is still linked to this space, but the user has no access to *space modules*.


## Module activation and configuration

*Users module* is activated by default and can't be deactivated. It is only accessible to *space admins*.

## Users management
See [Users management](../../../manager/users)

A *User* is linked to an account. Within a space, it can be attributed different *roles* , like *admin*, *manager*, *user* or *visitor*.
Creating a *user* creates an *account*. A *user account* can be member of multiple spaces.

## Set a space admin
See [Promote a user to space administration](../../../manager/users/#promote-a-user-to-space-administration)

## Link users to a client

You can link users to a client by clicking the *Client accounts* button for a user.

![basic configuration](../../../users_module_4.png)

Then, just choose a client amongst the Clients selector and click *Add*.

![basic configuration](../../../users_module_5.png)

## Manage users booking access

By default, users are not authorized to book resources. To make users able to book a resource, you have to manage their booking access.
Note that, since you need to have activated the *Booking module* to display the *Booking access* button aside the users names.

![basic configuration](../../../users_module_1.png)

Click the *Booking access* button for the user you want to be able to book a resource.

![basic configuration](../../../users_module_2.png)

Then click *Add* button (*i.e.* "add" this the user to the list of users who can book this resource) for the resource you want them to be able to book.

![basic configuration](../../../users_module_3.png)

Fill *Activation date* and, eventually, *Visa* if you set your *Booking module* to require visas.

[comment]: # (When editing this doc, not filling visa returns an error)
