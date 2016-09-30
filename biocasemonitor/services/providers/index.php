<?php

/**
 *
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/services/providers/index.php
 * @brief webservices providers
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

/**
 * get providers
 * @param int $id
 * @param string $name
 */
function getProviders($id, $name) {
    global $db;
    try {
        $sql = "SELECT
                    institution.id,
                    institution.shortname,
                    institution.name,
                    institution.url,
                    institution.pywrapper as biocase_url
                FROM institution
                WHERE active = '1'";
        $values = array();
        if (!empty($id)) {
            $sql .= " AND institution.id LIKE :id";
            $values[":id"] = $id;
        }
        if (!empty($name)) {
            $sql .= " AND institution.shortname LIKE :name";
            $values[":name"] = $name;
        }
        $sql .= " ORDER BY institution.shortname";


        $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $stmt->execute($values);

        $result = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        return json_encode($result, JSON_PRETTY_PRINT);
    } catch (\PDOException $e) {
        $output = array();
        $output["error"] = $e->getMessage();
        return json_encode($output);
    }
}

header('Content-type: application/json, charset=utf-8');

$id = filter_input(INPUT_GET, 'provider') || 0;
$name = filter_input(INPUT_GET, 'name');

echo getProviders($id, $name);

