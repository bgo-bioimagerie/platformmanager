#!/bin/bash

shibd  -c /etc/shibboleth/shibboleth2.xml start
/entrypoint.sh
