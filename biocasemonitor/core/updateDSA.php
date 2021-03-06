<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/updateDSA.php
 * @brief backend: update Data Source Access Point
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

$pr_id = $_POST["pr_name"];
$ds = $_POST["ds"];


$output = array();

$data = array();
foreach ($_POST as $key => $val) {
    $data[$key] = $val;
}
$ary = array();
$ary[] = $data;


try {
    foreach ($ary as $elt) {
        $sql = "UPDATE collection "
                . "SET url=:url, title=:title, title_slug=:title_slug, filter=:filter, dataset=:dataset,"
                . "active=:active, "
                . "landingpage_url=:landingpage_url, preferred_landingpage=:preferred_landingpage "
                . "WHERE id=:id ";

        $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (trim($elt["title"]) == "new Title" || empty($elt["title"])) {
            $elt["title"] = $elt["title_slug"];
        }
        if (empty($elt["final_filter"])) {
            $elt["final_filter"] = '<like path="/DataSets/DataSet/Metadata/Description/Representation/Title">' . $elt["dataset"] . '</like>';
        }

        $values = array(
            ":url" => $elt["url"],
            ":title_slug" => $elt["title_slug"],
            ":title" => $elt["title"],
            ":landingpage_url" => $elt["landingpage_url"],
            ":preferred_landingpage" => $elt["preferred_landingpage"],
            ":filter" => $elt["final_filter"],
            ":dataset" => $elt["dataset"],
            ":active" => $elt["active"],
            ":id" => $elt["id"]
        );

        $output[] = array(
            "url" => $elt["url"],
            "title_slug" => $elt["title_slug"],
            "title" => $elt["title"],
            "landingpage_url" => $elt["landingpage_url"],
            "preferred_landingpage" => $elt["preferred_landingpage"],
            "filter" => $elt["final_filter"],
            "dataset" => $elt["dataset"],
            "active" => $elt["active"],
            "id" => $elt["id"],
        );

        $stmt->execute($values);
    }
    echo json_encode($output, JSON_FORCE_OBJECT);
} catch (PDOException $e) {
    $output[] = $e->getMessage();
    $output[] = $e->getTraceAsString();
    echo json_encode($output, JSON_FORCE_OBJECT);
}
