<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
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
                institution.id as provider_id,
                institution.shortname as provider_shortname,
                institution.name as provider_name,
                institution.url as provider_url,
                institution.pywrapper as biocase_url,
                collection.title_slug as dsa,
                collection.dataset,
                collection.id as dataset_id,
                GROUP_CONCAT(archive.id || ';' || archive.link || ';' || archive.is_latest) as xml_archives
            FROM archive
            JOIN collection ON collection.id = archive.collection_id
            JOIN institution ON collection.institution_id = institution.id
            WHERE 1
                AND archive.collection_id = '$idDSA'
                AND archive.title='BioCASe Archive'
                AND collection.active = '1'
            GROUP BY
                collection.dataset
            ORDER BY
                archive.is_latest, institution.shortname, collection.dataset 
            ";


            //return json_encode(str_replace("\n", " ", $sql));
            $stmt = $db->query($sql);
            $result = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $archive_list = explode(",", $row["xml_archives"]);
                $row["xml_archives"] = array();
                foreach ($archive_list as $elt) {
                    list($id, $arch, $latest) = explode(";", $elt);
                    $tmp = array();
                    $tmp["archive_id"] = $id;
                    $tmp["xml_archive"] = $arch;
                    $tmp["latest"] = $latest ? True : False;
                    $row["xml_archives"][] = $tmp;
                }
                //$result[$row["dataset"]] = $row;
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
                institution.id as provider_id,
                institution.shortname as provider_shortname,
                institution.name as provider_name,
                institution.url as provider_url,
                institution.pywrapper as biocase_url,
                collection.title_slug as dsa,
                collection.dataset,
                GROUP_CONCAT(archive.id || ';' || archive.link || ';' || archive.is_latest) as xml_archives
            FROM archive
            JOIN collection ON collection.id = archive.collection_id
            JOIN institution ON collection.institution_id = institution.id
            WHERE  1
                AND archive.institution_id = '$idProvider'
                AND archive.title='BioCASe Archive'
                AND collection.active = '1'
            GROUP BY
                collection.dataset
            ORDER BY
                archive.is_latest, institution.shortname, collection.dataset
            ";

            $stmt = $db->query($sql);
            $result = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $archive_list = explode(",", $row["xml_archives"]);
                $row["xml_archives"] = array();
                foreach ($archive_list as $elt) {
                    list($id, $arch, $latest) = explode(";", $elt);
                    $tmp = array();
                    $tmp["archive_id"] = $id;
                    $tmp["xml_archive"] = $arch;
                    $tmp["latest"] = $latest ? True : False;
                    $row["xml_archives"][] = $tmp;
                }
                //$result[$row["dataset"]] = $row;
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
                institution.name as provider_name,
                institution.id as provider_id,
                collection.title_slug as dsa,
                collection.dataset,
                 GROUP_CONCAT(archive.id || ';' || archive.link || ';' || archive.is_latest) as xml_archives
            FROM archive
            JOIN collection ON collection.id = archive.collection_id
            JOIN institution ON collection.institution_id = institution.id
            WHERE 1
                AND archive.title='BioCASe Archive'
                AND collection.active = '1'
            GROUP BY
                collection.dataset
            ORDER BY
                institution.shortname, collection.dataset, archive.is_latest

            ";

            $stmt = $db->query($sql);
            $result = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $archive_list = explode(",", $row["xml_archives"]);
                $row["xml_archives"] = array();
                foreach ($archive_list as $elt) {
                    list($id, $arch, $latest) = explode(";", $elt);
                    $tmp = array();
                    $tmp["archive_id"] = $id;
                    $tmp["xml_archive"] = $arch;
                    $tmp["latest"] = $latest ? True : False;
                    $row["xml_archives"][] = $tmp;
                }
                //$result[$row["dataset"]] = $row;
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


$idProvider = filter_input(INPUT_GET, 'provider_id');
$idDSA = filter_input(INPUT_GET, 'dataset_id');

echo getXmlArchives($idProvider, $idDSA);
