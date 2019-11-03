<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/services/data-sources/index.php
 * @brief webservices data sources
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

namespace Webservices;

require_once("../../config/config.php");

$idProvider = $_GET["provider"];

/**
 * get Datasources
 *
 * @param  $idProvider
 * @return json
 */
function getDataSources($idProvider) {
    global $db;
    try {
        $sql = "SELECT
                '" . DATACENTER_NAME . "' || institution.shortname as provider_datacenter,
                institution.shortname as provider_shortname,
                institution.name as provider_name,
                institution.url as provider_url,
                institution.pywrapper as biocase_url,
                collection.title_slug as datasource,
		collection.id as dataset_id,
                collection.dataset,
                useful_link.title as type,
                useful_link.link
            FROM useful_link
            JOIN collection ON collection.id = useful_link.collection_id
            JOIN institution ON collection.institution_id = institution.id
            WHERE
                collection.active = '1'
            ";
        // @todo: emulate OUTER JOIN to integrate useful_links.
        $sql = "SELECT
                '" . DATACENTER_NAME . "' || institution.shortname as provider_datacenter,
                institution.shortname as provider_shortname,
                institution.name as provider_name,
                institution.url as provider_url,
                institution.pywrapper as biocase_url,
                collection.title_slug as datasource,
		collection.id as dataset_id,
                collection.dataset
            FROM collection
            JOIN institution ON collection.institution_id = institution.id
            WHERE
                collection.active = '1'
            ";

        if (!empty($idProvider)) {
            $sql .= " AND collection.institution_id = '$idProvider'";
        }
        $sql .= " ORDER BY institution.shortname, collection.title_slug";

        $stmt = $db->query($sql);

        $output = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        return json_encode($output, JSON_PRETTY_PRINT);
    } catch (\PDOException $e) {
        $output = array();
        $output["error"] = $e->getMessage() . $sql;
        echo json_encode($output);
    }
}

header('Content-type: application/json, charset=utf-8');
$idProvider = $_GET["provider_id"];
echo getDataSources($idProvider);
