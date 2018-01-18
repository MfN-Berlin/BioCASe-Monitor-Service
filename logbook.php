<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @namespace Consistency
 * @file biocasemonitor/logbook.php
 * @brief statistics to log resquests to the BPS installations
 *
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

session_start();
require_once("./config/config.php");

$idProvider = $_REQUEST["idProvider"];
$schema = $_REQUEST["schema"];
$dsa = $_REQUEST["dsa"];
$action = $_REQUEST["action"];
$concept = $_REQUEST["concept"];
$timeElapsed = $_REQUEST["timeElapsed"];

try {
    $sql = "INSERT INTO logbook (time_elapsed, concept, action, dsa, schema, institution_id)"
            . " VALUES (:time_elapsed, :concept, :action, :dsa, :schema, :institution_id)";
    $values = array(
        ":time_elapsed" => $timeElapsed,
        ":concept" => $concept,
        ":action" => $action,
        ":dsa" => $dsa,
        ":schema" => $schema,
        ":institution_id" => $idProvider
    );
    $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $stmt->execute($values);
} catch (\PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
