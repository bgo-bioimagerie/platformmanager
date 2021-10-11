---
title: "Helpdesk"
date: 2021-08-13T13:30:30+02:00
---

## Helpdesk for spaces

A lightweight helpdesk system to manage tickets from users to a space.

If pfm is configured with an imap account, let's say support@pfm.org, then
space members can send emails to support+spaceshortname@pfm.org to create a
ticket.

Managers can see and reply to all tickets, and ease the follow-ups of users issues,
with the full conversation.

Space email address is show in helpdesk configuration panel when activated.

Space members and managers can see tickets and manage them:

* set their status (new, open, assigned, pending reminder, closed)
* add private notes for managers
* reply to ticket (will send an email to user)

Users can reply to tickets email to add information to opened ticket.

When activated:

* emails sent from a space (comm, etc.) will have the helpdesk email address in the *from* of the email.
* if space has not defined a support address, the email address will also be shown in spaces home page.

Though not a full and complete helpdesk system like zammad, otrs, etc... this
module can help space managers to follow user issues when not having their own
helpdesk.
