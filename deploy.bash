#!/bin/bash
php standalone.php
php template.php
./ftp.bash --dir $REMOTE_DIR --host $REMOTE_HOST
curl -X PURGE http://extras.denverpost.com/app/record-tracker/index.html
