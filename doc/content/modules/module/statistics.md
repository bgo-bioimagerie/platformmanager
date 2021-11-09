---
title: "Statistics"
date: 2021-08-13T13:30:30+02:00
---

## Statistics dashboard

In space page, there is a link to the statistics dashboard, for managers only.

Managers can log in dashboard with their identifier and their API Key
(in user informations panel /usersmyaccount).

The *statistics dashboard* is based on [Grafana](https://grafana.com/docs/)
and several dashboards are set by default. Additional dashboards can be created
by managers.
It is advised not to modify provided dashboards as they may be overwritten on future
updates.

Each dashboard can be queried with time ranges, based on pfm recorded statistics.

Statistics are recorded in a space dedicated InfluxDB database, and queries follow
the [Flux](https://docs.influxdata.com/influxdb/cloud/query-data/flux/) language.

Each space uses a bucket named with the space *shortname*.
