
  BioCASe Monitor 2.1

  @copyright (C) 2013-2017 www.mfn-berlin.de
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

# Purpose

The BioCASe Monitor Service (BMS) is a tool for coordinators of networks of biodiversity databases 
that are based on the BioCASe Provider Software (BPS). 

The backend part of the tool 
allows registration of BioCASe providers and their data sources in a user-friendly UI.

In the frontend, all relevant information are displayed in an aggregated view, e.g.
- provider name (linking to the access point URL), 
- list of data sources, total number of records and of unique values per concept (i.e. number of units, number of multimedia objects), 
- link to the consistency checker tool
- link to archives
- other useful links.

In the EU funded project OpenUp! the BioCASe Monitor Service is used to monitor the progress of the provision of multimedia objects to Europeana. 

Furthermore, it is a useful tool for the coordinators of the content-providing work packages 
as a quality check of the mapping of the associated metadata in the ABCD or ABCDEFG schema 
and its required compliance with the Europeana Sematic Elements (ESE), the Europeana standard. 
The tool has been developed in close collaboration between the OpenUp! team (Work Package 4 & 7) 
and the GBIF-D team at the Museum für Naturkunde Berlin, Germany.

The version series 2.x code has been completely rewritten, and relies on a sqlite3 database.

A more detailed description has been published in the [Biodiversity Data Journal]  (https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3964725/)

# Webservices

As a complement of the GUI, the software offers Webservices. The entry point is [services/] (/services/)

_______________________________________

# Installation


## Requirements
- Webserver, e.g. apache2
- Database engine, e.g. sqlite3
- PDO
- PHP 5.6
- php5-curl, php5-sqlite, php5-xsl

## Installation steps
1. copy everything to a webfolder  
2. edit configuration file `config/config.php`
3. set files permissions
    - all files must be readable by webuser
    - DB file `provider.sqlite` must be writable by webuser
    - Cache folder `data_cache` must be writable by webuser   
4. create and populate database
    - run statements defined in `install/sampledata.sql`  

The following shell script `install/install.sh` does the job for an installation on linux. 
Maybe you are required to have sudoer's rights:

    #!/bin/bash
    # please edit these settings
    DBDIR=/my/db/folder/ 
    WEBDIR=/var/www/bms 
    WEBUSER=www-data  
    # running the commands
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
    chgrp $WEBUSER $DBDIR/provider.sqlite

## Backend

In the navigation bar, log in.
Default administrator account: username=admin, password=admin. 

## API Documentation

Point your browser to `info/doc/api/html/index.html`

## Typical Administrator's Workflow

1. Login

    * Login via the navigation bar
    * type in your credentials and validate
    * select a provider in the dropdown list

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

    - Data Sources
        * Data Sources are presented in tabs: a tab for each data set, one data source visible
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

    - Archives              
        * add an arbitrary list of archives
        * an archive is a URL pointing to a downloadable file
        * one archive must be marked as "latest"
                   
    - Useful Links
        * add an arbitrary list of links
        * a link is a URL pointing to a downloadable file and should be one of the following types:
            * BioCASe Query Tool
            * GBIF
            * Europaeana
            * Deutsche Digitale Bibliothek
            * GeoCASe
            * BiNHum
            * Pangaea
            * GFBio
        * example: `http://www.gbif.org/dataset/71f03224-f762-11e1-a439-00145eb45e9a` (GBIF)

7. Logout

    * log out via  the navigation bar

_______________________________________

# Issues

The BMS has been tested on Firefox and Chrome. It has been reported that Opera and IE do not display the data.
