<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/sortCountConcepts.php
 * @brief backend: sort Count Concepts
 *
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

$countId = $_POST["countId"];
$providerId = $_POST["pr_name"];

try {
    $affectedRows = 0;
    for ($j = 0; $j < count($countId); $j++) {
        $position = $j + 1;
        $sql = "UPDATE count_concept set position=:position WHERE institution_id=:institution_id AND id=:id";
        $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
            ":position" => $position,
            ":institution_id" => $providerId,
            ":id" => $countId[$j]
        );
        $stmt->execute($values);
        $affectedRows += $stmt->rowCount();
    }
    echo "$affectedRows rows affected.";
} catch (PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
