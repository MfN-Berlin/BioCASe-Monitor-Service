<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
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

$id = filter_input(INPUT_GET, 'provider');
$name = filter_input(INPUT_GET, 'name');

$dsa = filter_input(INPUT_GET, 'dsa');

//$url = filter_input(INPUT_GET, 'url');

$local_id = filter_input(INPUT_GET, 'localId');
$format = filter_input(INPUT_GET, 'format');



////////////////////////
// GET PYWRAPPER URL
//
try {
    $sql = "SELECT institution.pywrapper FROM institution WHERE 1";
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
        $result = $row["pywrapper"];
    }
} catch (\PDOException $e) {
    $result = $e->getMessage();
}
$pywrapper = $result;

$url = $pywrapper . "/pywrapper.cgi?dsa=" . $dsa;


/////////////////////////////////////
// CURL
//
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_REFERER, "http://www.naturkundemuseum.berlin");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
// Download the given URL, and return output
$xml_string = curl_exec($ch);
curl_close($ch);



/////////////////////
// XSLT
//
if ($format == "xml") {
    $xsltString = "";
} elseif ($format == "html") {
    $xsltString = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
<xsl:output method="xml" omit-xml-declaration="yes"/>

<xsl:template match="/">
    <xsl:apply-templates select="//biocase:capabilities"/>
</xsl:template>

<xsl:template match="//biocase:capabilities">
' . $local_id . '
   <select>
      <xsl:text>&#xa;</xsl:text>
      <option value="">please select a concept</option>
      <xsl:for-each select="//biocase:Concept">

       <xsl:variable name="currentValue" select="." />
       <option value="{$currentValue}" >
        <xsl:value-of select="."/>
       </option>
      </xsl:for-each>
      <xsl:text>&#xa;</xsl:text>
    </select>
 </xsl:template>

</xsl:stylesheet>';
} else {
    $xsltString = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
<xsl:output method="text" omit-xml-declaration="yes"/>

<xsl:template match="/">
    <xsl:text>{</xsl:text>
    <xsl:text>"localId":</xsl:text>"' . $local_id . '"<xsl:text>,</xsl:text>
    <xsl:text>&#xa;</xsl:text>

    <xsl:text>"url":</xsl:text>"' . $url . '"<xsl:text>,</xsl:text>
    <xsl:text>&#xa;</xsl:text>

    <xsl:text>"concepts":[</xsl:text>
    <xsl:text>&#xa;</xsl:text>
    <xsl:apply-templates select="//biocase:capabilities"/>
    <xsl:text>]}</xsl:text>
</xsl:template>

<xsl:template match="//biocase:capabilities">
    <xsl:text>""</xsl:text>
    <xsl:text>&#xa;</xsl:text>

    <xsl:for-each select="//biocase:Concept">
    <!--
        <xsl:text>,"</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>
        <xsl:text>&#xa;</xsl:text>
    -->
        <xsl:text>,{</xsl:text>
            <xsl:text>"dataset":</xsl:text>"<xsl:value-of select="."/>"
            <xsl:text>,"datatype":</xsl:text>"<xsl:value-of select="@datatype"/>"
            <xsl:text>,"searchable":</xsl:text>"<xsl:value-of select="@searchable"/>"
        <xsl:text>}</xsl:text>
    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>';
}

////////////
// OUTPUT


if ($format == "xml") {
    header('Content-type: text/xml charset=utf-8');
    echo $xml_string;
} elseif ($format == "html") {
    header('Content-type: text/html charset=utf-8');
    $xslt = new \XSLTProcessor();
    $xslt->importStylesheet(new \SimpleXMLElement($xsltString));
    echo $xslt->transformToXml(new \SimpleXMLElement($xml_string));
} else {
    header('Content-type: application/json charset=utf-8');
    $xslt = new \XSLTProcessor();
    try {
        $xslt->importStylesheet(new \SimpleXMLElement($xsltString));
        echo $xslt->transformToXml(new \SimpleXMLElement($xml_string));
    } catch (\PDOException $e) {
        $output = array();
        $output["error"] = $e->getMessage() . $sql;
        echo json_encode($output);
    }
}
