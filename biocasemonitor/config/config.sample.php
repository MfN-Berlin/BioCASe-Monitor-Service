<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/config/config.sample.php
 * @brief configuration: constants and DB settings
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bms;

/**
 * set global time zone
 */
date_default_timezone_set("Europe/Berlin");

/**
 *   set datacenter denomination
 */
define("DATACENTER_NAME", "Data Center ");

/**
 * set salt value for password encryption
 */
define("SALT", "WE@leht-=13");

/**
 * set verbose level
 */
define("VERBOSE", 3);

/**
 * set debug level (0 or 1)
 */
define("DEBUGMODE", 1);

/**
 * set interval for renewing the cache [in seconds]: every 7 days
 */
define("CACHING_INTERVAL", 604800); // 7 * 24 * 60 * 60

/**
 * set cache directory relative to root directory
 */
define("CACHE_DIRECTORY", "data_cache/");


/////////////////////////////
// DATABASE SETTINGS

/**
 * sqlite3 set database folder
 *
 */
define("DB_DIR", "/path/to/db/folder");

/**
 * sqlite3 set database filename
 */
define("DB_FILENAME", "sample-db.sqlite");

/**
 * database handler
 * @var $db
 */
$db = null;

/**
 * establish database connection
 */
function init() {
    global $db;
    try {
        $db = new \PDO("sqlite:" . DB_DIR . DIRECTORY_SEPARATOR . DB_FILENAME);
    } catch (\PDOException $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
}

init();



/////////////////////////////////////////////
// FROM HERE ON PLEASE DO NOT CHANGE ANYTHING

/**
 * current version of the BMS
 */
define("_VERSION", "2.0");

/**
 * all records
 */
define("TOTAL", 1);

/**
 * all distinct records
 */
define("DISTINCT", 2);

/**
 * all dropped records
 */
define("DROPPED", 4);


/**
 * json pretty printer, not interpreted with php verson < 5.4
 */
if (!defined("JSON_PRETTY_PRINT")) {
    define("JSON_PRETTY_PRINT", 128);
}
