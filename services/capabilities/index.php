<?php

/**
 * BioCASe Monitor 2.1
 *
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/services/capabilities/index.php
 * @brief webservices capabilities
 *
 * example call:
 * /services/capabilities/?provider=bgbm&dsa=Herbar
 * /services/capabilities/?provider=2&dsa=Herbar
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


$dsa_name = $_GET['dsa'];
$dsa_id = $_GET['dataset_id'];

$provider_name = $_GET['name'];
$provider_id = $_GET['provider_id'];

$format = $_GET['format'];

$schema = $_GET['schema'] ? $_GET['schema'] : 'http://www.tdwg.org/schemas/abcd/2.06';


////////////////////////////
// GET DEFAULT PYWRAPPER URL
//
try {
    $sql = "SELECT institution.pywrapper FROM institution WHERE 1";
    $values = array();
    if (!empty($provider_id)) {
        $sql .= " AND institution.id LIKE :id";
        $values[":id"] = $provider_id;
    }
    if (!empty($provider_name)) {
        $sql .= " AND institution.shortname LIKE :name";
        $values[":name"] = $provider_name;
    }
    $sql .= " ORDER BY institution.shortname";

    $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $stmt->execute($values);

    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $pywrapper = $row["pywrapper"];
    }
} catch (\PDOException $e) {
    $pywrapper = $e->getMessage();
}
$pywrapper = $result;

$default_url = $pywrapper . "/pywrapper.cgi?dsa=" . $dsa_name;




////////////////////////////
// GET ALTERNATIVE PYWRAPPER URL
//
try {
    $sql = "SELECT url FROM collection WHERE 1";
    $values = array();
    if (!empty($provider_id)) {
        $sql .= " AND institution_id LIKE :id";
        $values[":id"] = $provider_id;
    }
    if (!empty($dsa_id)) {
        $sql .= " AND id LIKE :dsa";
        $values[":dsa"] = $dsa_id;
    } elseif (!empty($dsa_name)) {
        $sql .= " AND title_slug LIKE :dsa";
        $values[":dsa"] = $dsa_name;
    }

    $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $stmt->execute($values);

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    $alt_url = $row["url"];
} catch (\PDOException $e) {
    $alt_url = $e->getMessage();
}


$query_url = ($alt_url ? $alt_url : $default_url);


/////////////////////////////////////
// CURL
//
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $query_url);
curl_setopt($ch, CURLOPT_REFERER, "http://www.naturkundemuseum.berlin");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
$xml_string = curl_exec($ch);
curl_close($ch);


if (strlen($xml_string) == 0) {
    $output = $query_url . " returns an empty response";
    if ($format == "xml") {
        header('Content-type: text/plain charset=utf-8');
        echo '<error>' . $output . '</error>';
    }
    if ($format == "json") {
        header('Content-type: application/json charset=utf-8');
        echo json_encode($output);
    }
    exit;
}

/////////////////////
// XSLT
//
if ($format == "xml") {
    $xsltString = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
<xsl:output method="xml" omit-xml-declaration="no" indent="yes"/>

    <xsl:template match="/">
    <capabilities>
        <supportedSchemas>
            <xsl:apply-templates  select="//biocase:SupportedSchemas"/>
        </supportedSchemas>
        <selectedSchema schema="' . $schema . '">
            <xsl:apply-templates select="//biocase:SupportedSchemas[@namespace=\'' . $schema . '\']/biocase:Concept" />
        </selectedSchema>
    </capabilities>
    </xsl:template>

   <xsl:template match="//biocase:SupportedSchemas">
         <xsl:for-each select=".">
            <supportedSchema>
                <xsl:value-of select="@namespace" />
            </supportedSchema>
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="//biocase:SupportedSchemas[@namespace=\'' . $schema . '\']/biocase:Concept">
        <xsl:for-each select=".">
            <element>
                <concept><xsl:value-of select="."/></concept>
                <datatype><xsl:value-of select="@datatype" /></datatype>
                <searchable><xsl:value-of select="@searchable" /></searchable>
           </element>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>';
} else {
    $xsltString = '<?xml version = "1.0" encoding = "UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
    <xsl:output method="text" omit-xml-declaration="yes"/>

    <xsl:template match="/">
        <xsl:text>{</xsl:text>

        <xsl:text>"url":</xsl:text>"' . $query_url . '"<xsl:text>,</xsl:text>
        <xsl:text>&#xa;</xsl:text>

        <xsl:text>"schemas":[""</xsl:text>
        <xsl:apply-templates select="//biocase:SupportedSchemas/@namespace"/>
        <xsl:text>],</xsl:text>
        <xsl:text>&#xa;</xsl:text>

        <xsl:text>"concepts":[""</xsl:text>
        <!-- <xsl:apply-templates select="//biocase:SupportedSchemas[@namespace=\'' . $schema . '\']" /> -->
        <xsl:apply-templates select="//biocase:SupportedSchemas" />
        <xsl:text>]}</xsl:text>
    </xsl:template>

    <xsl:template match="//biocase:SupportedSchemas/@namespace">
        <xsl:for-each select=".">
            <xsl:text>,"</xsl:text>
            <xsl:value-of select="." />
            <xsl:text>"</xsl:text>
        </xsl:for-each>
    </xsl:template>

    <!--<xsl:template match="//biocase:SupportedSchemas[@namespace=\'' . $schema . '\']" > -->
    <xsl:template match="//biocase:SupportedSchemas" >
        <xsl:for-each select="biocase:Concept" >
            <xsl:text>,</xsl:text>
            <xsl:text>&#xa;</xsl:text>
            <xsl:text>{</xsl:text>
            <xsl:text>  "dataset":</xsl:text>"<xsl:value-of select="."/>"
            <xsl:text>, "datatype":</xsl:text>"<xsl:value-of select="@datatype"/>"
            <xsl:text>, "searchable":</xsl:text>"<xsl:value-of select="@searchable"/>"
            <xsl:text>}</xsl:text>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>';
}

// @todo Unterarrays anlegen für jedes Schema
//////////////
// OUTPUT

if ($format == "xml") {
    header('Content-type: text/xml charset=utf-8');
    //echo $xml_string;
    $xslt = new \XSLTProcessor();
    try {
        $xslt->importStylesheet(new \SimpleXMLElement($xsltString));
        echo $xslt->transformToXml(new \SimpleXMLElement($xml_string));
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
} else {
    header('Content-type: application/json charset=utf-8');
    $xslt = new \XSLTProcessor();
    try {
        $xslt->importStylesheet(new \SimpleXMLElement($xsltString));
        echo $xslt->transformToXml(new \SimpleXMLElement($xml_string));
    } catch (\Exception $e) {
        $output = array();
        $output["error"] = $e->getMessage();
        echo json_encode($output);
    }
}
