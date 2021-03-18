<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/updateMainMetadata.php
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
    header('Location: index.php');
    exit;
}

$output = array();
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
    $output = $values;
} catch (PDOException $e) {
    $output[] = $e->getMessage();
    $output[] = $e->getTraceAsString();
}

echo json_encode($output);
