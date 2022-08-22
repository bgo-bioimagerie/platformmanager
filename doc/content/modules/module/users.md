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
- [manage users *client accounts*](./#link-clients-to-a-user)

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

Relatively to a space a user can be *pending*, *active*, or *inactive*.

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

## Users Access

Within the access page, you can manage users *space access*, *booking access* and *client accounts*.
It is accessible by clicking the *Access* button for the desired user.

### Manage users space access

Under this section, you can change their role and enter informations relative to their contract and to your platform convention.

![basic configuration](../../../users_module_1.png)

### Link clients to a user

You can link users to a client by clicking the *Client accounts* button from the access page.

Then, just choose a client amongst the Clients selector and click *Add*.

![basic configuration](../../../users_module_2.png)

### Manage users booking access

Depending on your booking settings, users may have to be authorized  in order to being able to book resources.
Booking authorizations target resources categories, not resources themselves.
To make users being able to book a resource, you have to manage their booking access.
Note that you need first to have activated the *Booking module* and set at least one category and one visa.

Click the *Booking access* button to move into the Booking authorizations section.

![basic configuration](../../../users_module_3.png)

Then select the category to add to user's authorisations, the related visa and click *Save* button for the resource you want them to be able to book.

Under *Authorisations for [username]* figures a summary of the current authorisations.

Under *Authorizations history for [username] figures an history of user's authorisations modifications.

