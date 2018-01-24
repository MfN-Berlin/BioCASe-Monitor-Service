<?php

/**
 * BioCASe Monitor 2.1
 * @copyright  (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @namespace Consistency
 * @file biocasemonitor/consistency/checkForErrors.php
 * @brief checks errors for given concept in the consistency check output
 *
 * @todo classify rules depending on the weight
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

$url = $_REQUEST["url"];
$filter = $_REQUEST["filter"];
$concept = $_REQUEST["concept"];
$schema = $_REQUEST["schema"];
$mapping = $_REQUEST["mapping"];


$debuginfo = array();

/**
 * get schema infos for given Schema 
 *
 * @param string $schema
 * @return array
 */
function getSchemaInfo($schema) {
    global $db;
    try {
        $sql = "SELECT * FROM schema WHERE urn='$schema' ";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result;
    } catch (\PDOException $e) {
        return array($e->getMessage());
    }
}

/**
 * get rule infos for given Concept and Schema mapping
 *
 * @param  string $concept  source element
 * @param  string $mapping  schema mapping
 * @return array
 */
function getRuleInfo($concept, $mapping) {
    global $db;
    try {
        $sql = "SELECT * FROM rule WHERE source_element='$concept' AND schema_mapping='$mapping'";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result;
    } catch (\PDOException $e) {
        return array($e->getMessage());
    }
}

$ruleInfo = getRuleInfo($concept, $mapping);


/////////////////////////////////////////////////////
//dispatching check-algorithms depending on the rules
//
$rules = explode(",", $ruleInfo["rule"]);

$done = false;
foreach ($rules as $rule) {
    $output = array();
    if (!$done && ($rule === "notEmpty" || $rule === "isURI" || $rule === "isCitation" || $rule === "isDateTime")) {

        $done = true;

        $request = '<?xml version="1.0" encoding="UTF-8"?>
            <request xmlns="http://www.biocase.org/schemas/protocol/1.3">
              <header><type>scan</type></header>
              <scan>
                <requestFormat>' . $schema . '</requestFormat>
                <concept>' . $concept . '</concept>
                <filter>' . $filter . '</filter>
              </scan>
            </request>';

        $xsltString = '<?xml version="1.0" encoding="UTF-8"?>
            <xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
            <xsl:output method="text" omit-xml-declaration="yes"/>

            <xsl:template match="/">
            <xsl-text>{</xsl-text>
                <xsl-text>"source_element":"' . $ruleInfo["source_element"] . '",</xsl-text>
                <xsl-text>"source_schema":"' . $schema . '",</xsl-text>
                <xsl-text>"reference":"' . $ruleInfo["reference"] . '",</xsl-text>
                <xsl-text>"rule":"' . $ruleInfo["rule"] . '",</xsl-text>
                <xsl-text>"weight":"' . $ruleInfo["weight"] . '",</xsl-text>
                <xsl-text>"tag":"' . $ruleInfo["tag"] . '",</xsl-text>

                <xsl:text>"content":"</xsl:text>
                    <xsl:apply-templates select="//biocase:value[1]" />
                <xsl:text>",</xsl:text>

                <xsl:text>"cardinal":</xsl:text><xsl:value-of select="//biocase:content/@recordCount"/>
            <xsl-text>}</xsl-text>
            </xsl:template>

<xsl:template match="//biocase:value[1]">
    <xsl:call-template name="escapeQuote">
      <xsl:with-param name="pText">
        <xsl:call-template name="jsonescape">
          <xsl:with-param name="str" select="." />
        </xsl:call-template>
      </xsl:with-param>
    </xsl:call-template>
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

  <xsl:template name="jsonescape">
   <xsl:param name="str" select="."/>
    <xsl:choose>
      <xsl:when test="contains($str, \'\\\')">
        <xsl:value-of select="concat(substring-before($str, \'\\\'), \'\\\\\' )"/>
        <xsl:call-template name="jsonescape">
          <xsl:with-param name="str" select="substring-after($str, \'\\\')"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
          <xsl:value-of select="$str"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

            </xsl:stylesheet>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $debuginfo[] = $url;
        $debuginfo[] = $httpcode;
        $debuginfo[] = $request;

        if ($httpcode != 200) {
            $output = array();
            $output["error"] = "http code:" . $httpcode;
            $output["debuginfo"] = $debuginfo;
            $json_output = json_encode($output);
        } else {
            // GET BODY
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $xml_string = curl_exec($ch);
            $xml_string = strtr($xml_string, "\r\n", "  ");
            curl_close($ch);

//            $debuginfo[] = $xml_string;
//            $output = array();
//            $output["debuginfo"] = $debuginfo;
//            echo json_encode($output); exit;


            $xslt = new \XSLTProcessor();
            $xslt->importStylesheet(new \SimpleXMLElement($xsltString));
            try {
                $output = $xslt->transformToXml(new \SimpleXMLElement($xml_string));
                $json_output = $output;
//                $output[] = $xslt->transformToXml(new \SimpleXMLElement($xml_string));
//                $json_output = json_encode($output);
            } catch (Exception $e) {
                $debuginfo[] = $e->getMessage();
                //$output = array();
                $output["error"] = $e->getMessage() . ": " . $e->getTraceAsString();
                $json_output = json_encode($output);
            }
        }
    }
}


if ($json_output) {
    echo $json_output;
} else {
    $conceptInfo["info"] = "no rules applied";
    $conceptInfo["source_schema_short"] = getSchemaInfo($schema)["shortname"];
    $conceptInfo["source_schema"] = $schema;
    $conceptInfo["content"] = "";
    $json_output = json_encode($conceptInfo);
    echo $json_output;
}


