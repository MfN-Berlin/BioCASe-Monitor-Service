<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/services/landingpages/index.php
 * @brief webservices landingpages (DataSet AND DataUnit)
 *
 * example call:
 *
 * provider=MfN
 * dsa=mfn_PAL
 *
 * DATA SET
 * filter=Fossil Invertebrates Ia
 *
 * DATA UNIT
 * inst=MfN
 * col=MfN - Fossil invertebrates Ia
 * cat=MB.Ga.3895
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
 *
 */

namespace Webservices;

require_once("../../config/config.php");

/**
 * get Infos about Data Provider
 *
 * @param  $idProvider
 * @return array
 */
function getProviderInfo($idProvider) {
    global $db;
    $provider = array();
    try {
        $sql = "SELECT
            institution.id as providerId,
            institution.name, institution.shortname, institution.url as providerUrl,
            institution.pywrapper
          FROM
            institution
          WHERE
            institution.id = '$idProvider' OR institution.shortname = '$idProvider' COLLATE NOCASE";
        $stmt = $db->query($sql);
        $provider = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $provider;
    } catch (\PDOException $e) {
        return array($e->getMessage());
    }
}

/**
 *  get URLs for landing pages
 *
 * @param array $provider
 * @param int $dsa
 * @param string $filter
 * @param string $inst
 * @param string $col
 * @param string $cat
 * @return json
 */
function getLandingpages($provider, $dsa, $filter, $inst, $col, $cat) {

    $alist = explode(",", $_SERVER["HTTP_X_FORWARDED_HOST"]);
    $server_name = $alist[0];
    if (!$server_name) {
        $server_name = $_SERVER["SERVER_NAME"];
    }

    $server_url = "http://" . $server_name . "/landingpage.php";

    $output = array();

    $output["provider"] = DATACENTER_NAME . " " . $provider["shortname"];

    $output["dataSet"] = $server_url . "?file=" . $provider["pywrapper"]
            . "/pywrapper.cgi?dsa=" . $dsa
            . "&filter=" . $filter;

    $output["dataUnit"] = $server_url . "?file=" . $provider["pywrapper"]
            . "/querytool/details.cgi?dsa=" . $dsa
            . "&detail=unit"
            . "&inst=" . $inst . "&col=" . $col . "&cat=" . $cat
    // . "&schema=http://www.tdwg.org/schemas/abcd/2.06"
    // . "&wrapper_url=" . $file . "?dsa=" . $dsa
    ;

    return json_encode($output, JSON_FORCE_OBJECT);
}

$idProvider = trim(filter_input(INPUT_GET, 'provider'));
$dsa = trim(filter_input(INPUT_GET, 'dsa'));

//dataset
$filter = trim(filter_input(INPUT_GET, 'filter'));

//dataunit
$inst = trim(filter_input(INPUT_GET, 'inst'));
$col = trim(filter_input(INPUT_GET, 'col'));
$cat = trim(filter_input(INPUT_GET, 'cat'));

header('Content-type: application/json, charset=utf-8');
echo getLandingpages(
        getProviderInfo($idProvider), $dsa, $filter, $inst, $col, $cat
);
