<?php

/**
 * BioCASe Monitor 2.0
 * @copyright  (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/lib/getMessages.php
 * @brief loads system messages
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

/**
 * loads system messages
 *
 * @param $filter frontend=1, backend=2, frontend+backend=3
 * @returns JSON encoded object
 */
function loadMessages($filter) {
    global $db;
    try {
        $sql = "SELECT * FROM message WHERE target & " . $filter . " == target";
        $stmt = $db->query($sql);
        $result = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[$row["short"]] = $row["long"];
        }
        return json_encode($result);
    } catch (\PDOException $e) {
        return json_encode(array($e->getMessage()));
    }
}

echo loadMessages($_GET["filter"]);
