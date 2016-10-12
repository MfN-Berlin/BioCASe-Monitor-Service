
#  BioCASe Monitor Service 2.0


## Purpose

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

A more detailed description has been published in the [Biodiversity Data Journal] (https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3964725/)

## Webservices

As a complement of the GUI, the software offers Webservices. The entry point is [services/] (/services/)

_______________________________________

## Installation


### Requirements
- Webserver, e.g. apache2
- Database engine, e.g. sqlite3 
- PDO
- php5, php5-curl, php5-sqlite, php5-xsl


### Installation steps
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

_______________________________________
    
### Backend

Default administrator account: username=admin, password=admin.
Point your browser to `core/admin.php` and log in.


_______________________________________

### API Documentation

Point your browser to `info/doc/api/html/index.html`

_______________________________________


### Typical Administrator's Workflow



1. Login 

    * press the `login`icon in the top bar
    * type in username and password
    * select provider

2. Main Metadata 

    * edit the provider's Main Metadata

3. Count Concepts 

    * example: `/DataSet/Units/Unit/MultiMediaObjects/MultiMediaObject/FileURI`
    * modify a concept: autocompletion from all concepts defined in ABCD2
    * move a concept up and down, using the handle
    * save the current concept count
    * delete the current concept count
    * add a new concept count

4. Data Source Access Points (DSAs) 

    * Data Sources are presented in tabs: a tab for each data source, one data source visible
    * operations: `add`, `delete`, `save`
    * edit required fields
        * status: active/inactive
        * data-source: select select a specific Data Source in the dropdown list
        * title: type a human readable full title
        * data-set: select a specific Data Set in the dropdown list
        * landing-page: select type of landingpge:
            * automatically generated: this is the default landingpage, based on a scan request for the concept `/DataSets/DataSet/Units/Unit/MultiMediaObjects/MultiMediaObject/FileURI`. 
            * user-defined URL: enter an URL
    * for advanced users only:
        * filter: define a complex filter
        * Beware ! The filter is applied **as is** (not validated) to the request sent to the pywrapper

5. Useful Links 

    * add an arbitrary list of links
    * a link is a fqdn (full qualified domain name) and should be one of the following types:
        * BioCASe Query Tool
        * BioCASe Archive
        * GBIF
        * Europaeana
        * Deutsche Digitale Bibliothek
        * GeoCASe
        * BiNHum
        * Pangaea
        * GFBio
    * example: `http://www.gbif.org/dataset/71f03224-f762-11e1-a439-00145eb45e9a` (GBIF)
    * special case: `BioCASe Archive`
        * recommendation: BioCASe Archives should always be provided and placed first
        * remark: please note that an URL for a BioCASe Archive is arbitrary, i.e. it need not to be hosted on the biowikifarm.

6. Logout

    * press the `logout` icon in the top bar
