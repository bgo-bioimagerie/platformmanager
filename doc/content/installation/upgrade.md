---
title: "Upgrade"
date: 2021-06-23T16:05:10+02:00
weight: 4
---

For first installation, and for any later upgrade, at code root location:

    php cli/pfm-cli.php install

This will setup database i.e create tables, migrate/update schemas etc..

To know current version:

    php cli/pfm-cli.php version --db