#!/bin/bash
# please edit these environment variables
DBDIR=/my/db/folder/
WEBDIR=/var/www/bms
WEBUSER=www-data
# running the commands ...
mkdir $DBDIR
mkdir $WEBDIR
cd $WEBDIR
chgrp -R $WEBUSER .
chmod -R g+w $DBDIR
mkdir data_cache
chmod g+w data_cache
cp config/config.sample.php config/config.php
sqlite3 $DBDIR/provider.sqlite < config/sampledata.sql
chgrp $WEBUSER $DBDIR/provider.sqlite
