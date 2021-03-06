<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/getCountConcepts.php
 * @brief backend: get Count Concepts
 *
 * $concept is dependant from provider
 * $specifier:  TOTAL|DISTINCT|DROPPED = bitmap 1|2|4
 * $filter is dependant from DSA, and dependant from $specifier
 *
 * returns JSON
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
require("../lib/util.php");

define("TOTAL", 1);
define("DISTINCT", 2);
define("DROPPED", 4);

$url = filter_input(INPUT_GET, 'url');
$concept = filter_input(INPUT_GET, 'concept');
$specifier = filter_input(INPUT_GET, 'specifier');
$filter = filter_input(INPUT_GET, 'filter');
$nocache = intval(filter_input(INPUT_GET, 'nocache'));

function get_tag($tag, $xml) {
    $tag = preg_quote($tag);
    preg_match_all('|<' . $tag . '[^>]*>(.*?)</' . $tag . '>|', $xml, $matches, PREG_PATTERN_ORDER);
    if (count($matches[1])) {
        return $matches[1][0];
    } else {
        return "";
    }
}

//$cachedir = "../data_cache/";
//$cachefilename = sluggify($url . "-" . end(explode("/", $concept)) . "-" . get_tag("like", $filter));
//$cachefile_json = $cachedir . $cachefilename . ".json";
//$cachefile_xml = $cachedir . $cachefilename . ".xml";
//$cachefile = $cachefile_json;

header('Content-type: application/json, charset=utf-8');

// will hold json data
$output = '{"url":"' . $url . '","concept":"' . $concept . '"';

//if ($nocache != 1 && (file_exists($cachefile) && (time() - CACHING_INTERVAL < filemtime($cachefile)))) {
//    $output = file_get_contents($cachefile);
//    echo $output;
//    exit;
//} else
{
/////////////
// 1 // TOTAL
//////////////
    if ($specifier & TOTAL > 0) {
// ABCD2 SEARCH: computes total values per concept, including duplicates
        $request = '<?xml version="1.0" encoding="UTF-8"?>
  <request xmlns="http://www.biocase.org/schemas/protocol/1.3">
    <header><type>search</type></header>
    <search>
        <requestFormat>http://www.tdwg.org/schemas/abcd/2.06</requestFormat>
        <responseFormat start="0" limit="1000000">http://www.tdwg.org/schemas/abcd/2.06</responseFormat>
        <filter>'
                . $filter . '
            <isNotNull path="' . $concept . '"></isNotNull>
        </filter>
        <count>true</count>
    </search>
  </request>';

/////////////////////////////////////
// CURL
//
// FIRST GET ONLY HEADERS
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
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
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $xml_string = curl_exec($ch);
            curl_close($ch);
        } else {
            $output .= ',"error":"ERROR"}';
            echo $output;
            exit;
        }

/////////////////////
// XSLT
// to JSON
        $xsltString = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
<xsl:output method="text" omit-xml-declaration="yes"/>

<xsl:template match="/">

    <xsl:text>,"total":</xsl:text><xsl:value-of select="//biocase:count"/>

    <xsl:text>,"cacheinfo_search":' . time() . '</xsl:text>

    <xsl:text>,"debuginfo_search":</xsl:text>
         <xsl:text>"</xsl:text>
<!--
        <xsl:text> nocache=' . $nocache . '</xsl:text>
        <xsl:text> cachefile=' . $cachefile . '</xsl:text>

        <xsl:value-of disable-output-escaping="no" select="//biocase:diagnostic[contains(.,' . '\'Executing SQL:\'' . ')]" />
-->
       <xsl:text>"</xsl:text>

</xsl:template>

</xsl:stylesheet>';

        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet(new \SimpleXMLElement($xsltString));

// JSON OUTPUT
        try {
            if ($xml_string) {
                $output .= $xslt->transformToXml(new \SimpleXMLElement($xml_string));
            } else {
                $output .= ',"error":"' . $url . " " . $request . '"';
            }
        } catch (Exception $e) {
            $output .= ',"error":"' . $e->getMessage() . '"';
        }
    }


////////////////////////////
// 2 // DISTINCT // DROPPED
////////////////////////////
// ABCD2 SCAN: distinct values per concept
    if (($specifier & (DISTINCT | DROPPED)) > 0) {
        $request = '<?xml version="1.0" encoding="UTF-8"?>
<request xmlns="http://www.biocase.org/schemas/protocol/1.3">
  <header><type>scan</type></header>
  <scan>
    <requestFormat>http://www.tdwg.org/schemas/abcd/2.06</requestFormat>
    <concept>' . $concept . '</concept>
    <filter>' . $filter . '</filter>
  </scan>
</request>';

/////////////////////////////////////
// CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $xml_string = curl_exec($ch);
        curl_close($ch);


/////////////////////
// XSLT
// to JSON
        $xsltString = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
<xsl:output method="text" omit-xml-declaration="yes"/>

<xsl:template match="/">';

        if (($specifier & DISTINCT) > 0) {
            $xsltString .= '<xsl:text>,"distinct":</xsl:text><xsl:value-of select="//biocase:content/@recordCount"/>';
        }
        if (($specifier & DROPPED) > 0) {
            $xsltString .= '<xsl:text>,"dropped":</xsl:text><xsl:value-of select="//biocase:content/@recordDropped"/>';
        }

        $xsltString .= '
    <xsl:text>,"cacheinfo_scan":' . time() . '</xsl:text>

    <xsl:text>,"debuginfo_scan":</xsl:text>
        <xsl:text>"</xsl:text>
          <xsl:text> specifier=' . $specifier . '</xsl:text>
<!--
            <xsl:text> nocache=' . $nocache . '</xsl:text>
            <xsl:text> cachefile=' . $cachefile . '*</xsl:text>

    <xsl:value-of disable-output-escaping="no"
        select="//biocase:diagnostic[contains(.,' . '\'Executing SQL:\'' . ')]"/>
    <xsl:value-of disable-output-escaping="yes"
        select="//biocase:diagnostic[contains(.,' . '\'Executing SQL:\'' . ')]"/>
-->
       <xsl:text>"</xsl:text>

</xsl:template>

</xsl:stylesheet>';

        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet(new \SimpleXMLElement($xsltString));

// JSON  OUTPUT
        try {
            if ($xml_string) {
                $output .= $xslt->transformToXml(new \SimpleXMLElement($xml_string));
            } else {
                $output .= ',"error":"' . $url . " " . $request . '"';
            }
        } catch (Exception $e) {
            $output .= ',"error":"' . $e->getMessage() . '"';
        }

        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet(new \SimpleXMLElement($xsltString));
    }

    $output .= "}";

    //file_put_contents($cachefile, $output . "\n");
    //file_put_contents($cachefile_xml, $xml_string . "\n");

    echo $output;
}
