<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/addCount.php
 * @brief backend: add a Count Concept
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
session_start();
require_once("../config/config.php");

if (!$_SESSION["authenticated"]) {
    header('Location: admin.php');
    exit;
}

$debugModePlus = 1;
$debugMode = $_POST['debug'] | $debugModePlus;
$providerId = $_POST['pr_name'];
$xpath = $_POST['xpath'];
$specifier = $_POST['specifier'];

$newXpath = $xpath[count($xpath)];
$newSpecifier = $specifier[count($specifier)];

$result = array();
$result["info"] = "";
$result["id"] = 0;

try {
    $sql = "SELECT max(position) as maxpos FROM count_concept "
            . "WHERE institution_id='$providerId'";
    $stmt = $db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $newPosition = 1 + $row["maxpos"];
    if ($debugMode) {
        $result["info"] .= "\n$sql =>  $newPosition\n";
    }

    $sql = "INSERT INTO count_concept "
            . "(position, institution_id, xpath, specifier) VALUES "
            . "('$newPosition','$providerId','$newXpath','$newSpecifier') ";
    if ($debugMode) {
        $result["info"] .= "\n" . $sql;
    }
    $stmt = $db->query($sql);

    // rather do a LAST_INSERT() statement
    $sql = "SELECT max(id) as newId FROM count_concept "
            . "WHERE position='$newPosition' AND institution_id='$providerId'";
    if ($debugMode) {
        $result["info"] .= "\n" . $sql;
    }
    $stmt = $db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $newId = $row["newId"];
    if ($debugMode) {
        $result["info"] .= " ==> newId=" . $newId;
    }

    $result["id"] = $newId;
    echo json_encode($result, JSON_PRETTY_PRINT);

    //$db = NULL;  // Close database
} catch (PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
