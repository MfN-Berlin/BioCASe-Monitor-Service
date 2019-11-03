<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/services/useful-links/index.php
 * @brief webservices useful links
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
 * webservice get useful links
 *
 * @param  $idDSA
 * @param  $idProvider
 * @return json
 */
function getUsefulLinks($idDSA, $idProvider) {
    global $db;
    if (!empty($idDSA)) {
        try {
            $sql = "SELECT
                    useful_link.id as link_id,
                    useful_link.institution_id as provider_id,
                    useful_link.collection_id as dataset_id,
                    useful_link.is_latest,
                    useful_link.title, useful_link.link, link_category.logo
                FROM useful_link
                LEFT OUTER JOIN link_category
                ON useful_link.title = link_category.name
                WHERE useful_link.collection_id = '$idDSA'
                ORDER BY useful_link.position";
//       $sql = "SELECT
//                '" . DATACENTER_NAME . "' || institution.shortname as provider_datacenter,
//                institution.url as provider_url,
//                institution.id as provider,
//                collection.title_slug as dsa,
//                collection.dataset,
//                useful_link.id,
//                useful_link.title as useful_link_type,
//                useful_link.link as useful_link_url
//            FROM useful_link
//            JOIN collection ON collection.id = useful_link.collection_id
//            JOIN institution ON collection.institution_id = institution.id
//            WHERE useful_link.collection_id = '$idDSA'
//            ";
            $stmt = $db->query($sql);
            $provider = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $provider[] = $row;
            }
            return json_encode($provider, JSON_PRETTY_PRINT);
        } catch (\PDOException $e) {
            return json_encode($e->getMessage());
        }
    } elseif (!empty($idProvider)) {
        try {
            $sql = "SELECT
                    useful_link.id as link_id,
                    useful_link.institution_id as provider_id,
                    useful_link.collection_id as dataset_id,
                    useful_link.is_latest,
                    useful_link.title, useful_link.link, link_category.logo
                FROM useful_link
                LEFT OUTER JOIN link_category
                ON useful_link.title = link_category.name
                WHERE  useful_link.institution_id = '$idProvider'
                ORDER BY useful_link.position";
//        $sql = "SELECT
//                '" . DATACENTER_NAME . "' || institution.shortname as provider_datacenter,
//                institution.url as provider_url,
//                institution.id as provider,
//                collection.title_slug as dsa,
//                collection.dataset,
//                useful_link.id,
//                useful_link.title as useful_link_type,
//                useful_link.link as useful_link_url
//            FROM useful_link
//            JOIN collection ON collection.id = useful_link.collection_id
//            JOIN institution ON collection.institution_id = institution.id
//            WHERE  useful_link.institution_id = '$idProvider'
//            ";
            $stmt = $db->query($sql);
            $provider = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                array_push($provider, $row);
            }
            return json_encode($provider, JSON_PRETTY_PRINT);
        } catch (\PDOException $e) {
            $output = array();
            $output["error"] = $e->getMessage();
            return json_encode($output);
        }
    } else {
        return json_encode(array());
    }
}

$idDSA = filter_input(INPUT_GET, 'dataset_id');
$idProvider = filter_input(INPUT_GET, 'provider_id');

header('Content-type: application/json, charset=utf-8');

echo getUsefulLinks($idDSA, $idProvider);

