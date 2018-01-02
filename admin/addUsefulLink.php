<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/admin/addUsefulLink.php
 * @brief backend: add a useful Link
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
    header('Location: index.php');
    exit;
}

$providerId = $_POST['idProvider'];
$collectionId = $_POST['idDSA'];

$result = array();
$result["info"] = "";
$result["id"] = 0;

try {
    $sql = "SELECT max(position) as maxpos FROM useful_link "
            . "WHERE institution_id='$providerId' "
            . "AND collection_id='$collectionId'";
    $stmt = $db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $newPosition = 1 + $row["maxpos"];
    if (DEBUGMODE) {
        $result["info"] .= "$sql =>  $newPosition ";
    }

    // ADD RECORD
    $sql = "INSERT INTO useful_link "
            . "(position, institution_id, collection_id, title, link) VALUES "
            . "('$newPosition','$providerId','$collectionId','','') ";
    if (DEBUGMODE) {
        $result["info"] .= $sql;
    }
    $stmt = $db->query($sql);

    // GET ID
    $sql = "SELECT id as newId FROM useful_link "
            . "WHERE position='$newPosition' "
            . "AND institution_id='$providerId' "
            . "AND collection_id='$collectionId'";
    if (DEBUGMODE) {
        $result["info"] .= $sql;
    }
    $stmt = $db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $newId = $row["newId"];
    if (DEBUGMODE) {
        $result["info"] .= " ==> newId=" . $newId;
    }

    $result["id"] = $newId;
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    $result["info"] .= $e->getMessage();
    $result["info"] .= $e->getTraceAsString();
    echo json_encode($result, JSON_PRETTY_PRINT);
}
