<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/getLinkCategories.php
 * @brief backend: get Link Categories
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
require_once("../config/config.php");
session_start();
if (!$_SESSION["authenticated"]) {
    header('Location: admin.php');
    exit;
}
header('Content-type: application/json, charset=utf-8');

try {
    $sql = "SELECT * FROM link_category";
    $stmt = $db->query($sql);
// JSON
    $provider = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($provider, $row);
    }
    echo json_encode($provider, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
