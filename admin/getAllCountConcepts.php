<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/getAllCountConcepts.php
 * @brief backend: get all Count Concepts
 * @todo filter through given schema
 * @todo move it to services
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

$term = filter_input(INPUT_GET, 'query');

$schema = filter_input(INPUT_GET, 'schema');

try {
    $sql = "SELECT concept.source_element, concept.source_schema
                FROM  concept
                JOIN schema ON concept.source_schema = schema.shortname
                WHERE 1
                AND concept.source_schema = :schema 
                AND concept.source_element like :term ";

    $values = array(
        ":schema" => $schema,
        ":term" => "%" . $term . "%"
    );

    $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $stmt->execute($values);

    $bs_result = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $record = [];
        $record["label"] = $row["source_element"];
        $record["id"] = $row["source_element"];

        $bs_result[] = $record;
    }
} catch (PDOException $e) {
    $bs_result = array();
    $bs_result[] = $e->getMessage();
    $bs_result[] = $e->getTraceAsString();
}

// OUTPUT
header('Content-type: application/json, charset=utf-8');

echo json_encode($bs_result, JSON_FORCE_OBJECT);
