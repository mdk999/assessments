#!/usr/bin/bash

set -e

cd /var/project/backend && npm install

sleep 1

cd /var/project/frontend && npm install

sleep 1

exit 0