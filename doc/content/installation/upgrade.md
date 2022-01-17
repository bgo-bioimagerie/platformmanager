---
title: "Upgrade"
date: 2021-06-23T16:05:10+02:00
weight: 4
---
## Installation/upgrade

For first installation, and for any later upgrade, at code root location:

    php cli/pfm-cli.php install

This will setup database i.e create tables, migrate/update schemas etc..

To know current version:

    php cli/pfm-cli.php version --db

## Developpers

To add an upgrade (not an install from scratch) script:

    php cli/pfm-cli.php upgrade --desc "fix this for that"

this will create a unique script in db/upgrade with a timestamp prefix.

You can then edit script to add whatever is needed for upgrade.

At *install* script will look for all scripts in db/upgrade (sorted by timestamp),
and apply them one by one (and record its id in db).

If script has already been applied, it is skipped.
