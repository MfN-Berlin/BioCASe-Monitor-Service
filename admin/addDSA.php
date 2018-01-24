<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/addDSA.php
 * @brief backend: add a DSA Point
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
session_start();
require_once("../config/config.php");

if (!$_SESSION["authenticated"]) {
    header('Location: index.php');
    exit;
}


$institution_id = $_POST['key'];

$newData = array();
$newData["info"] = "";
$newData["id"] = 0;
$newData["url"] = "";
$newData["pywrapper"] = "";
$newData["title"] = "new Title";
$newData["title_slug"] = "";
$newData["filter"] = "";

try {

    $sql = "SELECT pywrapper from institution WHERE id='$institution_id'";
    $stmt = $db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (count($row)) {
        $newData["url"] = $row["pywrapper"];
    }
    $newData["info"] .= $sql;


    $sql = "INSERT INTO collection (institution_id, url, title, title_slug, filter)"
            . " VALUES (:institution_id, :url, :title, :title_slug, :filter)";
    $values = array(
        ":institution_id" => $institution_id,
        ":url" => $newData["url"],
        ":title" => $newData["title"],
        ":title_slug" => $newData["title_slug"],
        ":filter" => $newData["filter"]
    );
    $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $stmt->execute($values);
    if (DEBUGMODE) {
        $newData["info"] .= ";\n" . $sql;
    }

    // @todo: rather do a LAST_INSERT() statement
    $sql = "SELECT max(id) as newId FROM collection WHERE institution_id='$institution_id'";
    if (DEBUGMODE) {
        $newData["info"] .= ";\n" . $sql;
    }
    $stmt = $db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (count($row)) {
        $newId = $row["newId"];
        $newData["id"] = $newId;
    }
    echo json_encode($newData, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    if (DEBUGMODE) {
        $newData["info"] .= $e->getMessage();
    }
    echo json_encode($newData, JSON_PRETTY_PRINT);
}
