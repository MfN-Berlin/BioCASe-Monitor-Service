<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/getDatasourceSchema.php
 * @brief backend: get Source Schema for given DataSource
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

require_once("../config/config.php");

session_start();

if (!$_SESSION["authenticated"]) {
    header('Location: index.php');
    exit;
}

header('Content-type: application/json, charset=utf-8');

$dsa = $_GET["dsa"];

if (isset($dsa)) {
    try {
        $sql = "SELECT title_slug as dsa, schema FROM collection WHERE id=:id";
        $stmt = $db->prepare($sql);
        $values = array(":id" => $dsa);
        $stmt->execute($values);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        echo json_encode($row, JSON_FORCE_OBJECT);
    } catch (\PDOException $e) {
        $output = array();
        $output[] = $e->getMessage();
        $output[] = $e->getTraceAsString();
        echo json_encode($output, JSON_FORCE_OBJECT);
    }
}


