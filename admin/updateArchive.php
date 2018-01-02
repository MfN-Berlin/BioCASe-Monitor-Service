<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/admin/updateArchive.php
 * @brief backend: update XML Archive
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

header('Content-Type: application/json');

$is_latest =  $_POST["is_latest"] == "false" ? 0 : 1;

try {
    $sql1 = "UPDATE archive set institution_id=:provider, link=:link, is_latest=:is_latest WHERE id=:id";
    $stmt1 = $db->prepare($sql1, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $values1 = array(
        ":id" => $_POST["id"],
        ":provider" => $_POST["provider"],
        ":link" => $_POST["link"],
        ":is_latest" => $is_latest
    );
    $stmt1->execute($values1);

  
 
    $sql2 = "UPDATE archive set is_latest=:is_latest WHERE collection_id=:dsa AND id!=:id";
    $stmt2 = $db->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $values2 = array(
        ":id" => $_POST["id"],
        ":dsa" => $_POST["dsa"],
        ":is_latest" => ($is_latest + 1) % 2
    );
    $stmt2->execute($values2);

    $out = array();
    $out[] = "success";
    $out[] = $sql1;
    $out[] = implode("/", $values1);
    $out[] = $sql2;
    $out[] = implode("/", $values2);
    echo json_encode($out, JSON_FORCE_OBJECT);
} catch (PDOException $e) {
    $out = array();
    $out[] = "error";
    $out[] = $e->getMessage();
    $out[] = $e->getTraceAsString();
    $out[] = $sql;
    $out[] = implode("/", $values);
    echo json_encode($out, JSON_FORCE_OBJECT);
}
