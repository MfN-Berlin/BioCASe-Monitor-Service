<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/getProviderMainData.php
 * @brief backend: get Main Data from given provider
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
//namespace Bms;


require_once("../config/config.php");
session_start();
if (!$_SESSION["authenticated"]) {
    header('Location: admin.php');
    exit;
}

header('Content-type: application/json, charset=utf-8');

$keyProvider = filter_input(INPUT_GET, 'key');
if (isset($keyProvider)) {
    try {
        $sql = "SELECT
            collection.*,
            institution.id as providerId,
            institution.name, institution.shortname, institution.url as providerUrl,
            institution.pywrapper,
            count_concept.xpath, count_concept.specifier
          FROM
            institution, collection, count_concept
          WHERE
            institution.id = collection.institution_id
            AND count_concept.institution_id = collection.institution_id
            AND count_concept.institution_id = $keyProvider
          ORDER BY
            collection.id, count_concept.position";

        $stmt = $db->query($sql);

        $provider = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($provider, $row);
        }
        echo json_encode($provider, JSON_PRETTY_PRINT);
    } catch (PDOException $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
}
