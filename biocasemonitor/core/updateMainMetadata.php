<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/updateMainMetadata.php
 * @brief backend: update main metedata for a given provider
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

//$ui = 0;
//if (isset($_POST["pr_ui"])) {
//    foreach ($_POST["pr_ui"] as $v) {
//        $ui += intval($v);
//    }
//}

try {
    $sql = "UPDATE institution set "
            . "name=:name, "
            . "shortname=:shortname, "
            . "url=:url, "
            . "pywrapper=:pywrapper "
            . "WHERE id=:id";
    $values = array(
        ":id" => $_POST["pr_name"],
        ":name" => $_POST["pr_name_edit"],
        ":shortname" => $_POST["pr_shortname_edit"],
        ":url" => $_POST["pr_url_edit"],
        ":pywrapper" => $_POST["pr_pywrapper"]
    );
    $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $stmt->execute($values);
    echo print_r($values);
} catch (PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
