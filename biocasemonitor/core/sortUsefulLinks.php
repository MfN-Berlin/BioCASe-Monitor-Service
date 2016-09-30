<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/sortUsefulLinks.php
 * @brief backend: sort Useful Links
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
    header('Location: admin.php');
    exit;
}

$idDSA = filter_input(INPUT_GET, 'key');

if (isset($idDSA)) {
    try {
        foreach ($_GET["item" . $idDSA] as $k => $v) {
            $newPosition = $k + 1;
            $sql = "UPDATE useful_link set position='$newPosition' "
                    . "WHERE id='$v' AND collection_id='$idDSA'";
            $stmt = $db->query($sql);
            echo "\n" . $sql;
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
}
