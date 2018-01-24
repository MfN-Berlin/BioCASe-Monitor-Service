<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @namespace Consistency
 * @file biocasemonitor/consistency/getSchemaMappings.php
 * @brief get schema mappings from DB
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

namespace Consistency;

session_start();
require_once("../config/config.php");

/**
 * get Schema Mappings
 * 
 * @todo  add parameter dataset, and restrict result to supported Schemas
 * @param string $mapping
 * @return string JSON object
 */
function getSchemaMappings($mapping) {
    global $db;
    try {
        $sql = "SELECT
        schema_mapping.name as mapping,
        source.urn as source_schema_urn,
        target.urn as target_schema_urn
    FROM schema_mapping
    INNER JOIN schema ON schema.shortname=schema_mapping.source_schema
    INNER JOIN schema AS source ON schema_mapping.source_schema = source.shortname
    INNER JOIN schema AS target ON schema_mapping.target_schema = target.shortname ";

//        if ($mapping)
//            $sql .= " WHERE 1  AND schema_mapping.name='" . $mapping . "'";
//        $sql .= " ORDER BY schema_mapping.name";

        $stmt = $db->query($sql);
        // JSON
        $result = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            array_push($result, $row);
        }
        return json_encode($result, JSON_PRETTY_PRINT);
    } catch (\PDOException $e) {
        return $e->getMessage();
    }
}

header('Content-type: application/json, charset=utf-8');

$mapping = $_REQUEST["mapping"];

echo getSchemaMappings($mapping);
