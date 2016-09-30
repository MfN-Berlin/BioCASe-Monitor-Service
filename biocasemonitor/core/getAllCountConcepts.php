<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/getAllCountConcepts.php
 * @brief backend: get all Count Concepts
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
require_once("../config/config.php");
session_start();
if (!$_SESSION["authenticated"]) {
    header('Location: admin.php');
    exit;
}

$typedIn = filter_input(INPUT_GET, 'term');

// approach: all ABCD concepts are stored in a DB table
try {
    $sql = "SELECT
                DISTINCT abcd_concept.abcd
                FROM  abcd_concept
                WHERE abcd_concept.abcd like '%" . $typedIn . "%'";
    $stmt = $db->query($sql);

    $result = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($result, $row["abcd"]);
    }
} catch (PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}


// approach: all ABCD concepts stored in a text file
//$result = file("../lib/AbcdConcepts.txt");
// OUTPUT
header('Content-type: application/json, charset=utf-8');
echo json_encode(preg_grep("/$typedIn/i", $result));
