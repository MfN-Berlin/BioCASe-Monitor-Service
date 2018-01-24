<?php

/**
 * BioCASe Monitor 2.1
 * @copyright  (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @namespace Consistency
 * @file biocasemonitor/consistency/getExampleValues.php
 * @brief get example values for given concept in the consistency check output
 *
 * @todo compute element xpath with correct prefix for given schema
 *
 *
 * @note
 * Example call: 
 * ./consistency/getExampleValues.php?
 *  url=http://biocase.naturkundemuseum-berlin.de/current/pywrapper.cgi?dsa=EDIT_ATBI
 *  &concept=/DataSets/DataSet/Units/Unit/MultiMediaObjects/MultiMediaObject/FileURI
 *  &filter=<like path="/DataSets/DataSet/Metadata/Description/Representation/Title">EDIT - ATBI in Spreewald (Germany)</like>
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

require_once("../config/config.php");
require("../lib/util.php");

$schema = $_REQUEST["schema"];
$url = $_REQUEST["url"];
$filter = $_REQUEST["filter"];
$concept = $_REQUEST["concept"];
$provider = $_REQUEST["provider"];
$nocache = $_REQUEST["nocache"];

/**
 * build array of parts of a concept, auxiliary step to add schema prefix to each part
 */
$aconcept = explode("/", $concept);
/**
 *  @todo compute element xpath with correct prefix for given schema
 */
$concept_xpath = implode("/abcd:", $aconcept);


$debuginfo = array();
$json_output = "";


/////////////////////////////////////
// CURL

$request = '<?xml version="1.0" encoding="UTF-8"?>
    <request xmlns="http://www.biocase.org/schemas/protocol/1.3">
      <header><type>scan</type></header>
      <scan>
            <requestFormat>' . $schema . '</requestFormat>
            <concept>' . $concept . '</concept>
            <filter>'
        . $filter . '
            </filter>
            <count>false</count>
      </scan>
    </request>';


// FIRST GET ONLY HEADERS
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$debuginfo[] = $httpcode;


if ($httpcode != 200) {
    $debuginfo["error"] = "error";
    $debuginfo["request"] = $request;
    $debuginfo["examples"] = "";
    $json_output = json_encode($debuginfo);
} else {
    // GET BODY
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    $xml_string = curl_exec($ch);
    $xml_string = strtr($xml_string, "\r\n", "  ");
    curl_close($ch);



/////////////////////
// XSLT

    $xsltString = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    	xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3"
	xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06"
>
<xsl:output method="text" omit-xml-declaration="yes"/>

<xsl:template match="/">
    <xsl-text>{</xsl-text>

    <xsl-text>"httpcode":' . $httpcode . ',</xsl-text>

     <xsl-text>"examples":[""</xsl-text>
        <xsl:apply-templates select="//biocase:value" />
     <xsl-text>]</xsl-text>
    <xsl-text>}</xsl-text>
</xsl:template>

<xsl:template match="//biocase:value" >
    <xsl:for-each select=".">
        <xsl-text>,"</xsl-text>
        <xsl:call-template name="escapeQuote"/>
        <xsl-text>"</xsl-text>
     </xsl:for-each>
</xsl:template>

<xsl:template name="escapeQuote">
      <xsl:param name="pText" select="."/>

      <xsl:if test="string-length($pText) >0">
       <xsl:value-of select="substring-before(concat($pText, \'&quot;\'), \'&quot;\')"/>

       <xsl:if test="contains($pText, \'&quot;\')">
        <xsl:text>\"</xsl:text>

        <xsl:call-template name="escapeQuote">
          <xsl:with-param name="pText" select=
          "substring-after($pText, \'&quot;\')"/>
        </xsl:call-template>
       </xsl:if>
      </xsl:if>
</xsl:template>

</xsl:stylesheet>';



    $xslt = new \XSLTProcessor();
    $xslt->importStylesheet(new \SimpleXMLElement($xsltString));

    try {
        $json_output = $xslt->transformToXml(new \SimpleXMLElement($xml_string));
    } catch (\Exception $e) {
        $debuginfo[] = $e->getMessage();
        $output = array();
        $output["error"] = $e->getMessage() . ": " . $e->getTraceAsString();
        $json_output = json_encode($output);
    }
}

echo $json_output;
