
  BioCASe Monitor 2.0

  @copyright (C) 2015 www.mfn-berlin.de
  @author  thomas.pfuhl@mfn-berlin.de
  based on Version 1.4 written by falko.gloeckler@mfn-berlin.de

 
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.
 
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 
  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

_______________________________________

# Bms

The BioCASe Monitor Service (BMS) is a tool for coordinators of networks of biodiversity databases 
that are based on the BioCASe Provider Software (BPS). 

The backend part of the tool 
allows registration of BioCASe providers and their data sources in a user-friendly UI.

In the frontend, all relevant information are displayed in an aggregated view, e.g.
- provider name (linking to the access point URL), 
- list of data sources, total number of records and of unique values per concept 
(i.e. number of units, number of multimedia objects), 
- link to the consistency checker tool
- other useful links.

In the EU funded project OpenUp! the BioCASe Monitor Service is used 
to monitor the progress of the provision of multimedia objects to Europeana. 

Furthermore, it is a useful tool for the coordinators of the content-providing work packages 
as a quality check of the mapping of the associated metadata in the ABCD or ABCDEFG schema 
and its required compliance with the Europeana Sematic Elements (ESE), the Europeana standard. 
The tool has been developed in close collaboration between the OpenUp! team (Work Package 4 & 7) 
and the GBIF-D team at the Museum für Naturkunde Berlin, Germany.

The version series 2.x code has been completely rewritten, and relies on a sqlite3 database.

# Webservices

As a complement of the GUI, the software offers Webservices. The entry point is [services/] (/services/)

_______________________________________

# Installation


## Requirements
- Webserver, e.g. apache2
- Database engine, e.g. sqlite3 
- PDO
- php5, php5-curl, php5-sqlite, php5-xsl


## Installation steps
1. edit the configuration file `config/config.php`
2. set file permissions
    - all files must be readable by webuser
    - DB file `provider.sqlite` must be writable by webuser
    - Cache folder `data_cache` must be writable by webuser   
3. populate database: 
    - run statements defined in `config/sampledata.sql`  

Go to the `config` folder and run the following shell script `install.sh`.
Probably you must have sudoer rights. 
For a non-Unix-like OS please adapt the script to your needs.

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

    
### Backend

Default administrator account: username=admin, password=admin.
Point your browser to `core/admin.php` and log in.

### API Documentation

Point your browser to `info/doc/api/html/index.html`
