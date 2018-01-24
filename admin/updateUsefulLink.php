<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/updateUsefulLink.php
 * @brief backend: update useful link
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

try {
    $sql = "UPDATE useful_link set institution_id=:provider, title=:title, link=:link, is_latest=:is_latest  WHERE id=:id";
    $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $values = array(
        ":id" => $_POST["id"],
        ":provider" => $_POST["provider"],
        ":title" => trim($_POST["title"]),
        ":link" => $_POST["link"],
        ":is_latest" => $_POST["is_latest"] == "false" ? 0 : 1,
    );
    $result = $stmt->execute($values);



    $sql = "SELECT logo FROM link_category WHERE name LIKE :name";
    $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $values = array(":name" => trim(strtoupper($_POST["title"])));
    $result = $stmt->execute($values);
    $output = "";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output = $row["logo"];
    }

    echo $output;
} catch (PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
