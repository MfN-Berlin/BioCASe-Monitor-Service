<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/getDataSetTitles.php
 * @brief backend: get Dataset Titles
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
require_once("../config/config.php");
session_start();
if (!$_SESSION["authenticated"]) {
    header('Location: index.php');
    exit;
}

require_once("../lib/util.php");

$url = filter_input(INPUT_GET, 'url');
$dsa = filter_input(INPUT_GET, 'idDSA');
//$dsa = explode("&", explode("dsa=", filter_input(INPUT_GET, 'url'))[1])[0];
$schema = filter_input(INPUT_GET, 'schema');
if (empty($schema)) {
    $schema = DEFAULT_SCHEMA;
}


$query = <<<QUERY
<?xml version='1.0' encoding='UTF-8'?>
    <request xmlns='http://www.biocase.org/schemas/protocol/1.3'>
        <header>
            <type>scan</type>
        </header>
        <scan>
            <requestFormat>$schema</requestFormat>
            <concept>/DataSets/DataSet/Metadata/Description/Representation/Title</concept>
            <filter>/DataSets/DataSet/Metadata/Description/Representation/Title</filter>
        </scan>
    </request>
QUERY;

// FIRST GET ONLY HEADERS
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($query) . "&dsa=" . $dsa);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 200) {
    // GET BODY
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . "&query=" . urlencode($query) . "&dsa=" . $dsa);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $xml_string = curl_exec($ch);
    curl_close($ch);
} else {
    header('Content-type: application/json, charset=utf-8');
    echo "[]";
    //echo "HTTP Code: ";
    //print_r($httpcode);
    exit;
}

if (empty($xml_string)) {
    header('Content-type: application/json, charset=utf-8');
    $output = array();
    $output[] = "error";
    $output[] = "tried to get the list of all datasets";
    $output[] = "providerError";
    $output[] = "timeout=60s";
    $output = json_encode($output);
    echo $output,
    exit;
}


$xsltString = '
<?xml version="1.0" encoding="UTF-8"?>
    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
     xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
<xsl:output method="text" omit-xml-declaration="yes"/>

<xsl:template match="/">
    <xsl:text>["---"</xsl:text>
        <xsl:apply-templates select="//biocase:scan"/>
    <xsl:text>]</xsl:text>
</xsl:template>

<xsl:template match="//biocase:value">
    <xsl:text>,"</xsl:text>
    <xsl:value-of select="."/>
    <xsl:text>"</xsl:text>
</xsl:template>

</xsl:stylesheet>';

$xslt = new XSLTProcessor();
$xslt->importStylesheet(new SimpleXMLElement($xsltString));

try {
    $output = $xslt->transformToXml(new SimpleXMLElement($xml_string));
} catch (Exception $e) {
    $debuginfo[] = $e->getMessage();
    $output = array();
    $output[] = "error";
    $output[] = $e->getMessage();
    //$output[] = $e->getTraceAsString();
    $output = json_encode($output);
}

header('Content-type: application/json, charset=utf-8');
echo $output;
