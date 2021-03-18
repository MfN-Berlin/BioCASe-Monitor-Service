<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/sortArchives.php
 * @brief backend: sort BioCASe XML Archives
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

$idDSA = filter_input(INPUT_GET, 'key');

if (isset($idDSA)) {
    try {
        foreach ($_GET["item" . $idDSA] as $k => $v) {
            $newPosition = $k + 1;
            $sql = "UPDATE archive set position='$newPosition' "
                . "WHERE id='$v' AND collection_id='$idDSA'";
            $stmt = $db->query($sql);
            echo "\n" . $sql;
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
}
