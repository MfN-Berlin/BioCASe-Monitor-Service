<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file services/xml-archives/index.php
 * @brief Webservices xml-archives
 *
 * @section DESCRIPTION
 *
 * @details
 * This is a special case of the webservice get useful links
 * having the title 'BioCASe Archive'
 *
 */

namespace Webservices;

require_once("../../config/config.php");

/**
 * get XML Archives
 *
 * @param int $idProvider
 * @param int $idDSA
 * @return json
 */
function getXmlArchives($idProvider, $idDSA) {
    global $db;
    if (!empty($idDSA)) {
        try {
            $sql = "SELECT
                '" . DATACENTER_NAME . "' || institution.shortname as provider_datacenter,
                institution.shortname as provider_shortname,
                institution.name as provider_name,
                institution.url as provider_url,
                institution.pywrapper as biocase_url,
                collection.title_slug as dsa,
                collection.dataset,
                useful_link.link as xml_archive
            FROM useful_link
            JOIN collection ON collection.id = useful_link.collection_id
            JOIN institution ON collection.institution_id = institution.id
            WHERE 1
                AND useful_link.collection_id = '$idDSA'
                AND useful_link.title='BioCASe Archive'
                AND collection.active = '1'
            ";

            $stmt = $db->query($sql);
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
    } elseif (!empty($idProvider)) {
        try {
            $sql = "SELECT
                '" . DATACENTER_NAME . "' || institution.shortname as provider_datacenter,
                institution.shortname as provider_shortname,
                institution.name as provider_name,
                institution.url as provider_url,
                institution.pywrapper as biocase_url,
                collection.title_slug as dsa,
                collection.dataset,
                useful_link.link as xml_archive
            FROM useful_link
            JOIN collection ON collection.id = useful_link.collection_id
            JOIN institution ON collection.institution_id = institution.id
            WHERE  1
                AND useful_link.institution_id = '$idProvider'
                AND useful_link.title='BioCASe Archive'
                AND collection.active = '1'
            ";

            $stmt = $db->query($sql);
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
    } else {
        try {
            $sql = "SELECT
                '" . DATACENTER_NAME . "' || institution.shortname as provider_datacenter,
                institution.url as provider_url,
                collection.title_slug as dsa,
                collection.dataset,
                useful_link.link as xml_archive
            FROM useful_link
            JOIN collection ON collection.id = useful_link.collection_id
            JOIN institution ON collection.institution_id = institution.id
            WHERE 1
                AND useful_link.title='BioCASe Archive'
                AND collection.active = '1'
            ORDER BY
                institution.shortname
            ";

            $stmt = $db->query($sql);
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
}

header('Content-type: application/json, charset=utf-8');


$idProvider = filter_input(INPUT_GET, 'provider');
$idDSA = filter_input(INPUT_GET, 'dsa');

echo getXmlArchives($idProvider, $idDSA);
