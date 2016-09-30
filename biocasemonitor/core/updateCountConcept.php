<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/updateCountConcept.php
 * @brief backend: update Count Concept
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

$id = $_POST["id"];
$xpath = $_POST["xpath"];
$providerId = $_POST["pr_name"];
$specifier = 0;
foreach ($_POST["specifier"] as $value) {
    $specifier += $value;
}


try {
    $sql = "UPDATE count_concept set xpath=:xpath, specifier=:specifier "
            . "WHERE id=:id";
    $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $values = array(
        ":xpath" => trim($xpath),
        ":specifier" => $specifier,
        ":id" => $id
    );
    $stmt->execute($values);
    echo $id;
    echo " [" . trim($xpath) . "] " . $specifier;
} catch (PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
