<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/getDataSources.php
 * @brief backend: get Data Source Access Points
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

namespace Bms;

require_once("../config/config.php");
session_start();
if (!$_SESSION["authenticated"]) {
    header('Location: admin.php');
    exit;
}
require_once("../lib/util.php");

$dsa = filter_input(INPUT_GET, 'idDSA');
$url = filter_input(INPUT_GET, 'url');
$url .= "/index.cgi";

// FIRST GET ONLY HEADERS
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 0);
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
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $xml_string = curl_exec($ch);
    curl_close($ch);
} else {
    header('Content-type: application/json, charset=utf-8');
    echo "[]";
    exit;
}

$dirty = $xml_string;

$x = new \DOMDocument;
$x->loadHTML($dirty);
$clean = $x->saveXML();


$xml_string = $clean;

$xsltString = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
<xsl:output method="text" omit-xml-declaration="yes"/>

<xsl:template match="/">
    <xsl:text>["---"</xsl:text>
    <xsl:text>&#x0a;</xsl:text>
        <xsl:apply-templates select="/html/body/div/ul"/>
    <xsl:text>]</xsl:text>
</xsl:template>

<xsl:template match="li">
    <xsl:text>,"</xsl:text>
    <xsl:value-of select="."/>
    <xsl:text>"</xsl:text>
</xsl:template>

</xsl:stylesheet>';

$xslt = new \XSLTProcessor();
$xslt->importStylesheet(new \SimpleXMLElement($xsltString));


try {
    $output = $xslt->transformToXml(new \SimpleXMLElement($xml_string));
} catch (Exception $e) {
    $debuginfo[] = $e->getMessage();
    $output = array();
    $output[] = "error";
    $output[] = $e->getMessage();
    //$output["error"] = $e->getMessage() . ": " . $e->getTraceAsString();
    $output = json_encode($output);
}


$output_format = "application/json";
$output_format = "text/plain";
header('Content-type: ' . $output_format . ', charset=utf-8');
echo $output;
