<?php

/**
 *
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
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
 * get datasets
 * @param int $id
 */
function getDatasets($provider_id, $dataset_id) {
    global $db;
    try {
        $sql = "SELECT
		institution.id as provider_id,
		'" . DATACENTER_NAME . "' || institution.shortname as provider_datacenter,
                institution.shortname as provider_shortname,
                institution.name as provider_name,
                institution.url as provider_url,
                institution.pywrapper as biocase_url,
                collection.title_slug as datasource,
		collection.id as dataset_id,
                collection.dataset,
		collection.landingpage_url as custom_landingpage,
		a.xml_archives,
		u.useful_links
            FROM collection
            JOIN institution ON collection.institution_id = institution.id
	    LEFT JOIN 
		 (
			SELECT collection_id, GROUP_CONCAT(archive.id || ';' || archive.link || ';' || archive.is_latest) as xml_archives
			FROM archive
			GROUP BY collection_id
		 ) as a
		 ON a.collection_id = collection.id
	    LEFT JOIN 
		(
			SELECT collection_id, GROUP_CONCAT(useful_link.id || ';' || useful_link.title || ';' || useful_link.link || ';' || useful_link.is_latest) as useful_links
			FROM useful_link
			GROUP BY collection_id
			ORDER BY useful_link.position
                ) as u
		ON u.collection_id = collection.id
            WHERE
                collection.active = '1'
	    ".(!empty($provider_id) ? " AND institution.id='".$provider_id."'" : "")."
	    ".(!empty($dataset_id) ? " AND collection.id='".$dataset_id."'" : "")."
	";
   
       /* $values = array();
        if (!empty($provider_id)) {
            $sql .= " AND institution.id LIKE :pid";
            $values[":pid"] = $provider_id;
        }
        if (!empty($dataset_id)) {
            $sql .= " AND collection.id LIKE :cid";
            $values[":cid"] = $dataset_id;
        }*/
        $sql .= " ORDER BY institution.shortname";


        $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $stmt->execute($values);
        
	$result = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

	        $archive_list = explode(",", $row["xml_archives"]);
		if(count($archive_list)>0 && !empty($row["xml_archives"]))
		{
			$row["xml_archives"] = array();
                	foreach ($archive_list as $elt) {
                    		list($id, $arch, $latest) = explode(";", $elt);
                    		$tmp = array();
                    		$tmp["archive_id"] = $id;
                    		$tmp["xml_archive"] = $arch;
                    		$tmp["latest"] = $latest ? True : False;
                    		$row["xml_archives"][] = $tmp;
                	}
		}else
			$row["xml_archives"] = array();

	        $link_list = explode(",", $row["useful_links"]);
		if(count($link_list)>0 && !empty($row["useful_links"]))
		{
			$row["useful_links"] = array();
               		 foreach ($link_list as $elt) {
                    		list($id, $title, $url, $latest) = explode(";", $elt);
                    		$tmp = array();
                    		$tmp["link_id"] = $id;
                    		$tmp["title"] = $title;
                   		 $tmp["url"] = $url;
                    		$tmp["is_latest"] = $latest ? True : False;
                    		$row["useful_links"][] = $tmp;
                	}
		}else
			$row["useful_links"] = array();
	
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

$dataset_id = $_GET["dataset_id"];
$provider_id = $_GET["provider_id"];

echo getDatasets($provider_id, $dataset_id);

