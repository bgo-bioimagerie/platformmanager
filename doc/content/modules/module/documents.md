---
title: "Documents"
date: 2021-08-13T13:30:30+02:00
---

**Documents** module gives access to a basic documentation storage.

In the base plan, module allows space managers to upload/download
documents on a private basis only (limited to space managers).

A document name can use a path like naming to simulate a directory
hierarchy.

Example, with a document named *resource1/usage.pdf*, *usage.pdf* will
be displayed in *resource1* directory. Modifying document name will
update its location accordingly. This path is virtual, ie is used for
display only and ease document categorization.

With the search bar, you can search a document by its name (needs 3
characters minimum to start the search).

![document list](../../../documents_list.png)

If your space plan has the feature *Document visibility scopes*, then
a visibility field is added to documents.

A document can be scoped:

* private: only space managers can download
* public: anyone even anonymous users can download
* member: all space members can download
* user: only this user can download
* client: only users of this client can download

Space managers have access to all documents

Upload/edit is limited to space managers or admin (depending on module configuration)

![document creation](../../../documents_create.png)
