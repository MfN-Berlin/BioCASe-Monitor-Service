<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @namespace Consistency
 * @file biocasemonitor/consistency/checkRules.php
 * @brief get rules from DB table rule, get capabilities from BPS via CURL, check the rules
 * @todo CURL request can be time consuming
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

header('Content-type: application/json, charset=utf-8');

session_start();
require_once("../config/config.php");

$dsa = $_REQUEST["dsa"];
$filter = $_REQUEST["filter"];
$mapping = $_REQUEST["mapping"];

$debug = $_REQUEST["debug"];
$result = array();

/**
 * get Schemas
 *
 * @return array
 */
function getSchemas()
{
    global $db;
    try {
        $sql = "SELECT shortname, urn FROM schema";

        $stmt = $db->query($sql);
        $result = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[$row["urn"]] = $row["shortname"];
        }
        return $result;
    } catch (\PDOException $e) {
        return $e->getMessage();
    }
}

/**
 * get Schema Mapping Info
 *
 * @param  string $mapping
 * @return array
 */
function getSchemaMappingInfo($mapping)
{
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
        if ($mapping) {
            $sql .= " WHERE 1 AND schema_mapping.name='" . $mapping . "'";
        }
        $sql .= " ORDER BY schema_mapping.name";

        $stmt = $db->query($sql);

        $result = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        return $result;
    } catch (\PDOException $e) {
        return $e->getMessage();
    }
}

/**
 * get elements, without associated rules
 *
 * @param  string $mapping
 * @return array
 */
function getMappedElementsWithoutRules($mapping)
{
    global $db;
    try {
        /*
          mapping.source_element,
          mapping.target_element,
          mapping.schema_mapping
         */
        $sql = "SELECT concept.* FROM concept
			JOIN mapping on concept.source_element = mapping.source_element
			WHERE 1 ";
        if ($mapping) {
            $sql .= " AND mapping.schema_mapping = '$mapping' ";
        }
        $sql .= " ORDER BY mapping.source_element";

        $stmt = $db->query($sql);
        $elements = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            //$elements[] = $row;
            //$row["searchable"] = "unknown";
            $row["rule"] = "unknown";
            $row["tag"] = "unknown";
            $row["weight"] = "unknown";

            $elements[$row["source_element"]] = $row;
        }
    } catch (\PDOException $e) {
        $elements = array();
        $elements[] = $e->getMessage();
    }
    return $elements;
}

/**
 * get element pairs, with associated rule
 *
 * @param  string $mapping
 * @return array
 */
function getMappedElements($mapping)
{
    global $db;
    try {
        $sql = "SELECT
        concept.reference as source_reference,
        concept.source_element,
        mapping.target_element,
        mapping.schema_mapping,
        rule.rule, rule.tag, rule.weight 
    FROM concept
    LEFT JOIN mapping ON concept.source_element = mapping.source_element
    LEFT JOIN rule    ON rule.source_element    = mapping.source_element
    WHERE 1 ";
        if ($mapping) {
            $sql .= " AND mapping.schema_mapping = '$mapping' ";
        }
        $sql .= " ORDER BY mapping.source_element ";

        $stmt = $db->query($sql);
        $elements = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $elements[] = $row;
            //$elements[$row["source_element"]] = $row;
        }
    } catch (\PDOException $e) {
        $elements = array();
        $elements[] = $e->getMessage();
    }
    return $elements;
}

// some debug info
$debuginfo = array();


////////////////////////////
// get complex filter
$result["filter"] = urldecode($filter);
$debuginfo[] = "filter: " . $filter;

////////////////////////////
// get schema mapping info
$mapping_info = getSchemaMappingInfo($mapping);
$sourceSchema = $mapping_info[0]["source_schema_urn"];
$result["sourceSchema"] = $sourceSchema;

//////////////////////
// get mapped elements
$result["mapped_elements"] = getMappedElements($mapping);

$allMappedElements = getMappedElementsWithoutRules($mapping);
$result["allMappedElements"] = $allMappedElements;

////////////////////////////////////////
// get capabilities of selected schema
//

$alist = explode(",", $_SERVER["HTTP_X_FORWARDED_HOST"]);
$server_name = $alist[0];
if (!$server_name) {
    $server_name = $_SERVER["SERVER_NAME"];
}
$server_url = "http://" . $server_name . dirname($_SERVER['REQUEST_URI']);
if (PROXY_WORKAROUND) {
    $server_url = PROXY_WORKAROUND_URL . dirname($_SERVER['REQUEST_URI']);
}

$startTime = time();
$debuginfo[] = $server_url;

$file_contents = file_get_contents($server_url . "/../services/capabilities/?format=xml&dsa=$dsa&schema=$sourceSchema");
//$file_contents = readfile($server_url . "/../services/capabilities/?format=xml&dsa=$dsa&schema=$sourceSchema");

$debuginfo[] = $server_url . "/../services/capabilities/?format=xml&dsa=$dsa&schema=$sourceSchema";
$debuginfo[] = "time elapsed in ms:" . (time() - $startTime);
$debuginfo[] = "file length in bytes: " . strlen($file_contents);

$obj = (array) simplexml_load_string($file_contents);

$json = json_encode($obj);


$capabilities = (array) json_decode($json, true);

$result["capabilities"] = $capabilities["selectedSchema"]["element"];


/////////////////////////////
// get supported Schemas

$all_schemas = getSchemas();
$supportedSchemas = array();
$cap = $capabilities["supportedSchemas"]["supportedSchema"];
if (!is_array($cap))
    $cap = array($cap);
foreach ($cap as $elt) {
    $supportedSchemas[] = array($all_schemas[$elt], $elt);
}
if (count($supportedSchemas) > 0) {
    $result["supportedSchemas"] = $supportedSchemas;
} else {
    $the_only_schema = $capabilities["supportedSchemas"]["supportedSchema"];
    $result["supportedSchemas"] = array(array($all_schemas[$the_only_schema], $the_only_schema));
}

/////////////////////////////
// compute missing concepts
//

$concepts = array();
foreach ($result["capabilities"] as $row) {
    $concepts[] = $row["concept"];
}


$mandatory_elts = array();
foreach ($result["mapped_elements"] as $elt_with_rule) {
    if (strpos($elt_with_rule["rule"], "notEmpty") !== false || strpos($elt_with_rule . ["status"], "M") !== false) {
        $mandatory_elts[] = $elt_with_rule["source_element"];
    }
}


$missing = array_diff($mandatory_elts, $concepts);
$result["missing"] = array_values($missing);

//////////////////////////////////
// merge capabilities and elements
//
// build an associative array, using the concept field as key.


$debuginfo["sample_/DataSets/DataSet/ContentContacts/ContentContact/Address"] = $allMappedElements["/DataSets/DataSet/ContentContacts/ContentContact/Address"];
$debuginfo["sample_/DataSets/DataSet/ContentContacts/ContentContact/Name"] = $allMappedElementsElements["/DataSets/DataSet/ContentContacts/ContentContact/Name"];
$debuginfo["sample_/DataSets/DataSet/ContentContacts/ContentContact/Email"] = $allMappedElementsElements["/DataSets/DataSet/ContentContacts/ContentContact/Email"];

$checkedRecords = array();


foreach ($result["mapped_elements"] as $mapped_element) {

    $debuginfo["mapped_" . $mapped_element["source_element"]] = $mapped_element;

    // fields: source, target, reference, rules
    // copying source_element to concept
    $extended_concept = $mapped_element;
    $extended_concept["concept"] = $mapped_element["source_element"];

    $checkedRecords[$mapped_element["source_element"]] = $extended_concept;

    // merging with fields: concept,datatype,searchable
    foreach ($result["capabilities"] as $capability) {

        if (!array_key_exists($capability["concept"], $checkedRecords)) {

            $debuginfo["new_" . $capability["concept"]] = $allElements[$capability["concept"]];
            $tmp = $allMappedElements[$capability["concept"]];

            $checkedRecords[$capability["concept"]] = @array_merge($capability, $tmp);
            // ok
        } else if ($capability["concept"] == $mapped_element["source_element"]) {

            $debuginfo["already_" . $capability["concept"]] = $mapped_element;

            $checkedRecords[$capability["concept"]] = @array_merge($capability, $mapped_element);
            // ok
        } else {

            $debuginfo["else_" . $capability["concept"]] = $capability;
            //$tmp = array();
            //$tmp = $allElements[$capability["concept"]];
            //$checkedRecords[$capability["concept"]] = array_merge($capability , $tmp);
            //$checkedRecords[$capability["concept"]] = array_merge($capability , array());
        }
    }
}

// adding items not yet processed
//foreach ($result["mapped_elements"] as $mapped_element) 
//foreach ($result["capabilities"] as $capability) {
//
//    if (!array_key_exists($capability["concept"], $checkedRecords)) {
//
//        $debuginfo["last_" . $mapped_element["source_element"]] = $mapped_element;
//        //$checkedRecords[$mapped_element["source_element"]] = $result["capabilities"][$mapped_element["source_element"]];
//    }
//}

$result["checkedRecords"] = $checkedRecords;

//if ($debug) $result["debug"] = $debug;

echo json_encode($result);
exit;
