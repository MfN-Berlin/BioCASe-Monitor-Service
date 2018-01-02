#!/bin/bash
# please edit these settings
DBDIR=/my/db/folder/ 
WEBUSER=www-root  
WEBDIR=/var/www/bms 
# commands
mkdir $DBDIR  
mkdir $WEBDIR  
cd $WEBDIR  
chgrp -R $WEBUSER .  
chmod -R g+w $DBDIR   
mkdir data_cache  
chmod g+w data_cache 
cd config
ln -s config.sample.php config.php
mv sampledata.sql $DBDIR  
sqlite3 $DBDIR/provider.sqlite < $DBDIR/sampledata.sql