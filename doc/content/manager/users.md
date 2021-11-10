---
title: "Users management"
date: 2021-06-23T16:08:15+02:00
weight: 2
---

## Users creation

Users cannot register for an account, space managers must create the user for
their space if user does not already have an account
(they will receive a temporary password by email).
If user already have an account, he can request to join the space via the web
interface (Join button).

## Create a user as a space admin

If you want to directly link a user to a specific space or if you are *space admin*, not superadmin, you can create user from within a space by going into *Users module* in your space homepage.

![admin space homepage](../../install_12.png)

![user creation](../../create_user_5.png)

Once in *Users module* main page, you have to click *Add* at the bottom of the left side menu.
The *Create an account* screen displays.

![user creation](../../create_user_6.png)

When validated, an email is send to the new user with their credentials. User have now access to Platform-Manager.
To allow them access to this space, you still need to affect them a role.

Still in *Users module*, go into *Pending accounts*, at the top of the left side menu.

![user creation](../../create_user_5.png)

The user you just created is listed amongst the other pending users accounts.

![user creation](../../create_user_7.png)

You can choose to *Activate* or *Delete* them:

* *Delete* will detach users from this space. They will no longer appear in Pending accounts but they will still be able to connect to Platform-Manager since their account will not be deleted
* By clicking *Activate*, you will navigate to the activation screen where you can affect a role to the user.

    ![user creation](../../create_user_8.png)

When activated, users are notified by email that they have now access to your space.

If you go back to Active Users screen (and set the filter by letter to *All*), you can now see the user you just activated.

![user creation](../../create_user_9.png)

## Promote a user to space administration

There are two ways to set a user as space admin:

1. By setting their role to *admin* in *Users module*
2. By affecting user to space administration in *Space module*

### Setting user role to admin

Inside your space, go into *Users module* and click *Access* button for the user you want to set as space admin.

![user creation](../../create_user_9.png)

![admin access](../../give_admin_access_1.png)

Then, under *Role*, select *Admin*.
When validated (*Save* button), user will automatically rejoin Admin users list for this space.

### Affecting user to space administration

Inside your space, go into *Space module*.

![admin access](../../give_admin_access_2.png)

Then, under *Admin* selection, click *Add*.

![admin access](../../give_admin_access_3.png)

You can now select an admin for this space amongst space users.
When validated(*Save* button), user will automatically set to *Admin* for this space.
