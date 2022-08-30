---
title: "Getting started"
date: 2021-06-23T16:08:15+02:00
weight: 1
---
## Setup new spaces

At first, as superadmin you must create a space, create accounts for the future space administrators and give them *admin* role to this space.

First step is to connect with the configured admin account.

### Create a space

In the *admin* menu, go to *Spaces*

![main page](../../install_1.png)

![admin menu](../../install_2.png)

Add a space.

Note that you can choose to *pre-configure* your space. That will activate the base modules: [Booking](../../modules/module/booking/), [Resources](../../modules/module/resources) and [Clients](../../modules/module/clients)

![create space](../../install_3.png)

Space is created, now it is to create menus to access it.

![space created](../../install_4.png)

### Create menus

Menus are a way to group spaces in logical groups.

In *admin* menu, go to *Menus*

In *menus* section, create a *new menu*

![admin menus management](../../install_5.png)

![admin menus management](../../install_6.png)

Once created, create a *sub menus*

![admin menus management](../../install_7.png)

Link your submenu to previously created menu

![admin menus management](../../install_8.png)

![admin menus management](../../install_9.png)

At last, create a *items* that will link a submenu to a space

![admin menus management](../../install_10.png)

Now you main menu appears on top.

Clicking on menu will show linked spaces (submenus will appear only
if there are multiple spaces).

![admin menus management](../../install_11.png)

### Your new space

Then you can access your *space homepage* by clicking on its name at the top of the corresponding *item*.
The default components of a new space are *Space*, *Configuration*, *Users* and *History*.
In order to run this new space, you need to create new users an give them admin access to it.

![admin space homepage](../../install_12.png)

## Create a user

A *User* is linked to an account. Within a space, it can be attributed different *roles* , like *admin*, *manager*, *user* or *visitor*.
Creating a *user* creates an *account*.

There are two ways to create a new user:

- As a space admin
- As a superadmin

### Create a user as a space admin
See [Users management](../../manager/users/#create-a-user-as-a-space-admin)

### Create a user as a superadmin

In the *admin* menu, go to *Users*.

![user creation](../../create_user_1.png)

![user creation](../../create_user_2.png)

Add User.

![user creation](../../create_user_3.png)

User account is created.
Once you have transmitted them their credentials, they will be able to connect to Platform-Manager and ask to *join a space*.
A user can be member of multiple spaces.

![user creation](../../create_user_4.png)

## Set a space admin
See [Promote a user to space administration](../../manager/users/#promote-a-user-to-space-administration)

Now user can access space and configure it! :-)
