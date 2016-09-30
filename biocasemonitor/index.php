<?php
/**
 * BioCASe Monitor 2.0
 *
 * @copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/index.php
 * @brief entry point, using class definition of Bms
 * 
 * @license GNU General Public License 3
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

session_start();
if (!$_SESSION) {
    $_SESSION['authenticated'] = 0;
    $_SESSION['rights'] = 0;
    $_SESSION['provider'] = -1;

    $_SESSION["username"] = "guest";
    $_SESSION["fullname"] = "Guest";
    $_SESSION["email"] = "";
}
require_once("config/config.php");

/**
 * class definition of the BioCASe Monitor Service
 */
class Bms {

    /**
     * set debug mode, overwriting constant DEBUGMODE by GET parameter
     */
    public $debugmode = DEBUGMODE;

    /**
     *  holds the system messages
     */
    public $message = "";

    /**
     * helper to extract XML tag
     *
     * @param string $tag
     * @param string $xml
     * @return string
     */
    private function getTag($tag, $xml) {
        $tag = preg_quote($tag);
        $matches = "";
        preg_match_all('|<' . $tag . '[^>]*>(.*?)</' . $tag . '>|', $xml, $matches, PREG_PATTERN_ORDER);
        if (count($matches[1])) {
            return $matches[1][0];
        } else {
            return "";
        }
    }

    /**
     * get list of all concepts of a given provider
     *
     * @param int $idProvider
     * @return string json Object
     */
    function getConcepts($idProvider) {
        global $db;
        $output = array();
        if (isset($idProvider)) {
            try {
                $sql = "SELECT
            count_concept.id, count_concept.institution_id, count_concept.xpath, count_concept.specifier
           FROM
            count_concept
           WHERE
            count_concept.institution_id = '$idProvider'
           ORDER BY
            count_concept.position";

                $stmt = $db->query($sql);
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $output[] = $row;
                }
            } catch (\PDOException $e) {
                $output["error_message"] = $e->getMessage();
                $output["error_trace"] = $e->getTraceAsString();
            }
        }
        return json_encode($output);
    }

    /**
     * get Number of Current Records via a BPS search request
     *
     * @param string $url Query URL
     * @param string $filter complex filter in XML-format
     * @param int $providerId ID of Data Center
     * @param int $nocache 1 or 0
     * @return string json Object, e.g. { cardinal: 101, error: "", cacheinfo: "1467913058", debuginfo: "" }
     */
    function getCurrentRecords($url, $filter, $providerId, $nocache) {

        $provider_basics = $this->getProviderBasicInfos($providerId);
        $cache_path_url = parse_url($url, PHP_URL_QUERY);

        $cache_dir = CACHE_DIRECTORY . strtolower($provider_basics["shortname"]);
        @mkdir($cache_dir);
        $cachepath = $cache_dir . "/"
                . strtolower(preg_replace("/dsa=/", 'dsa+', $cache_path_url))
                . "+currentrecords.json";

        if ($nocache != 1 && (file_exists($cachepath) && (time() - CACHING_INTERVAL < filemtime($cachepath)))) {
            $json_string = file_get_contents($cachepath);
            return $json_string;
        } else {
///////////////////////////////////
// ABCD2 search
//
            $request = '<?xml version="1.0" encoding="UTF-8"?>
            <request xmlns="http://www.biocase.org/schemas/protocol/1.3">
                <header><type>search</type></header>
                <search>
                <requestFormat>http://www.tdwg.org/schemas/abcd/2.06</requestFormat>
                <responseFormat start = "0" limit = "10">http://www.tdwg.org/schemas/abcd/2.06</responseFormat>
                <filter></filter>
                <count>true</count>
                </search>
            </request>';

/////////////////////////////////////
// CURL
//
            $ch = curl_init();
//set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 2);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request) . "&filter=" . urlencode($filter));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $xml_string = curl_exec($ch);
            curl_close($ch);
///////////////////////////
// XSLT
//
            $xsl_sheet = '<?xml version = "1.0" encoding = "UTF-8"
    ?>
    <xsl:stylesheet version="1.0"
                    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                    xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3">
        <xsl:output method="text" omit-xml-declaration="yes"/>

        <xsl:template match="/">
            <xsl:text>{</xsl:text>

            <xsl:text>"cardinal":</xsl:text><xsl:value-of select="//biocase:count"/>

            <xsl:text>,"error":""</xsl:text>

            <xsl:text>,"cacheinfo":</xsl:text>
            <xsl:text>"</xsl:text>
            <xsl:text>' . time() . '</xsl:text>
            <xsl:text>"</xsl:text>

            <xsl:text>,"debuginfo":</xsl:text>
            <xsl:text>"</xsl:text>
            <!--
                <xsl:value-of disable-output-escaping="no"
                    select="//biocase:diagnostic[contains(.,' . '\'Executing SQL:\'' . ')]" />
            -->
            <xsl:text>"</xsl:text>
            <xsl:text>}</xsl:text>
        </xsl:template>

    </xsl:stylesheet>';

            $xslt = new \XSLTProcessor();
            $xslt->importStylesheet(new \SimpleXMLElement($xsl_sheet));

            try {
                $json_string = $xslt->transformToXml(new \SimpleXMLElement($xml_string));
                file_put_contents($cachepath, $json_string . "\n");
                return $json_string;
            } catch (Exception $e) {
                $output = array();
                $output["error"] = $e->getMessage();
                $output["cardinal"] = 0;
                $output["provider"] = $providerId;
                return json_encode($output);
            }
        }
        return "{}";
    }

    /**
     * get number of records satisfying a given concept via a BPS search or scan request
     *
     * @param string $providerId
     * @param string $url
     * @param string $concept
     * @param int $specifier bitmap TOTAL=1,DISTINCT=2,DROPPED=4
     * @param string $filter complex filter
     * @param int $nocache 1|0
     * @return string json Object
     */
    function getCountConcepts($providerId, $url, $concept, $specifier, $filter, $nocache) {

        $provider_basics = $this->getProviderBasicInfos($providerId);

        $cache_dir = CACHE_DIRECTORY . strtolower($provider_basics["shortname"]);
        @mkdir($cache_dir);
        $cachepath = $cache_dir . "/"
                . strtolower(preg_replace("/dsa=/", 'dsa+', parse_url($url, PHP_URL_QUERY)))
                . "+concept+" . end(explode("/", $concept))
                . "+filter+" . $this->sluggify($this->getTag("like", $filter))
                . ".json";

        if ($nocache != 1 && (file_exists($cachepath) && (time() - CACHING_INTERVAL < filemtime($cachepath)))) {
            $output = file_get_contents($cachepath);
            return $output;
        } else {

// $output will hold json data string
            $output = '{"url":"' . $url . '","concept":"' . $concept
                    . '","cachefile":"' . $cachepath . '","cached":' . ($nocache ? '"no"' : '"yes"');

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
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $xml_string = curl_exec($ch);
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
                    $output .= ',"total":"-1"';
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
            <!--
                <xsl:text>,"debuginfo_search":</xsl:text>
                    <xsl:text>"</xsl:text>
                     <xsl:text> nocache=' . $nocache . '</xsl:text>
                     <xsl:text> cachefile=' . $cachepath . '</xsl:text>

                     <xsl:value-of disable-output-escaping="no" select="//biocase:diagnostic[contains(.,' . '\'Executing SQL:\'' . ')]" />

                   <xsl:text>"</xsl:text>
            -->
        </xsl:template>

    </xsl:stylesheet>';

                $xslt = new \XSLTProcessor();
                $xslt->importStylesheet(new \SimpleXMLElement($xsltString));

// JSON OUTPUT
                try {
                    if ($xml_string) {
                        $output .= $xslt->transformToXml(new \SimpleXMLElement($xml_string));
                    } else {
                        $output .= ',"total_error":"empty xml source"';
                    }
                } catch (Exception $e) {
                    $output .= ',"total_error":"' . $e->getMessage() . '"';
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

            <!--
            <xsl:text>,"debuginfo_scan":</xsl:text>
            <xsl:text>"</xsl:text>
            <xsl:text> specifier=' . $specifier . '</xsl:text>
            <xsl:text> nocache=' . $nocache . '</xsl:text>
            <xsl:text> cachefile=' . $cachepath . '*</xsl:text>
            <xsl:value-of disable-output-escaping="no"
              select="//biocase:diagnostic[contains(.,' . '\'Executing SQL:\'' . ')]"/>
            <xsl:value-of disable-output-escaping="yes"
              select="//biocase:diagnostic[contains(.,' . '\'Executing SQL:\'' . ')]"/>
            <xsl:text>"</xsl:text>
            -->

        </xsl:template>

    </xsl:stylesheet>';

                $xslt = new \XSLTProcessor();
                $xslt->importStylesheet(new \SimpleXMLElement($xsltString));

// JSON  OUTPUT
                try {
                    $output .= $xslt->transformToXml(new \SimpleXMLElement($xml_string));
                } catch (Exception $e) {
                    $output .= ',"error":"' . $e->getMessage() . '"';
                }

                $xslt = new \XSLTProcessor();
                $xslt->importStylesheet(new \SimpleXMLElement($xsltString));
            }

            $output .= "}";

            file_put_contents($cachepath, $output . "\n");
            return $output;
        }
    }

    /**
     * get Citation of a DataSet
     *
     * @param string $providerId
     * @param string $url
     * @param string $filter
     * @param int $cached 0|1
     * @param string $concept "/DataSets/DataSet/Metadata/IPRStatements/Citations/Citation/Text"
     * @return string json Object
     */
    function getCitation($providerId, $url, $filter, $cached = 1, $concept = "/DataSets/DataSet/Metadata/IPRStatements/Citations/Citation/Text") {
        $aconcept = explode("/", $concept);
        $concept_xpath = implode("/abcd:", $aconcept);
        $debuginfo = array();
        $json_output = "";

        $provider_basics = $this->getProviderBasicInfos($providerId);
        $cache_path = parse_url($url, PHP_URL_QUERY) . "+citation+filter+" . $this->sluggify($filter);

        $cache_dir = CACHE_DIRECTORY . strtolower($provider_basics["shortname"]);
        @mkdir($cache_dir);
        $cachepath = $cache_dir . "/"
                . strtolower(preg_replace("/dsa=/", 'dsa+', $cache_path))
                . ".json";

        if ($cached && (file_exists($cachepath) && (time() - CACHING_INTERVAL < filemtime($cachepath)))) {
            $json_output = file_get_contents($cachepath);
        } else {

/////////////////////////////////////
// CURL
            $request = '<?xml version="1.0" encoding="UTF-8"?>
    <request xmlns="http://www.biocase.org/schemas/protocol/1.3">
        <header><type>search</type></header>
        <search>
            <requestFormat>http://www.tdwg.org/schemas/abcd/2.06</requestFormat>
            <responseFormat start="0" limit="200">http://www.tdwg.org/schemas/abcd/2.06</responseFormat>
            <filter>'
                    . $filter . '
                <isNotNull path="' . $concept . '"></isNotNull>
            </filter>
            <count>false</count>
        </search>
    </request>';

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
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 90);
                $xml_string = curl_exec($ch);
                curl_close($ch);
            } else {
                $debuginfo[] = $httpcode;
                $json_output = json_encode($debuginfo);
                return $json_output;
            }

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
            <xsl-text>"cacheinfo":"' . time() . '",</xsl-text>
            <xsl-text>"examples":[""</xsl-text>
            <xsl:apply-templates select="//biocase:content"/>
            <xsl-text>]}</xsl-text>
        </xsl:template>

        <xsl:template match="//biocase:content">
            <xsl:for-each select="/' . $concept_xpath . '">
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
                file_put_contents($cachepath, $json_output);
            } catch (Exception $e) {
                $debuginfo[] = $e->getMessage();
                $output = array();
                $output["error"] = $e->getMessage() . ": " . $e->getTraceAsString();
                $json_output = json_encode($output);
            }
        }
        return $json_output;
    }

    /**
     * count all possible CURL calls to BPS
     *
     * @return int $total
     */
    function getTotalMaxCalls() {
        global $db;
        $total = 0;

        // count the providers
        try {
            $sql = "SELECT id FROM institution WHERE active='1'";
            $stmt = $db->query($sql);
            $result = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $result[] = $row["id"];
            }
        } catch (\PDOException $e) {

        }

        for ($i = 0; $i < count($result); $i++) {
            $total += $this->getMaxCalls($result[$i]);
        }

        return $total;
    }

    /**
     * count all possible CURL calls to BPS of a given DSA
     *
     * @param int $idProvider
     * @return array [$idProvider, $total]
     */
    function getMaxCalls($idProvider) {
        global $db;

        $total = 0;

// count the concepts to be counted
        try {
            $sql = "SELECT count(id) as card FROM count_concept WHERE institution_id='$idProvider'";
            $stmt = $db->query($sql);
            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $total += $row["card"];
            }
            $total += 2; // add the calls to citation, currentRecords
        } catch (\PDOException $e) {

        }

// count the collections (DSA points)
        try {
            $sql = "SELECT count(id) as card FROM collection WHERE institution_id='$idProvider'
                    AND (
                        collection.active = '1'
                        OR
                        collection.id IN (SELECT id FROM collection WHERE institution_id='" . $_SESSION["provider"] . "')
                    )
                    ";
            $stmt = $db->query($sql);
            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $coeff = $row["card"];
            }
        } catch (\PDOException $e) {

        }

        $total *= $coeff;
        return $total;
    }

    /**
     * get main data of given provider
     *
     * @param int $idProvider
     * @return object JSON object
     */
    function getProviderMainData($idProvider) {
        global $db;
        try {
            $sql = "
            SELECT
                collection.*,
                count_concept.xpath,
                count_concept.specifier
            FROM
               collection, count_concept
            WHERE 1
                AND collection.institution_id = count_concept.institution_id
                AND collection.institution_id = '$idProvider'
                AND
                    (
                    collection.active = '1'
                    OR
                    collection.id IN (SELECT id FROM collection WHERE institution_id='" . $_SESSION["provider"] . "')
                    OR " . $_SESSION["provider"] . " = 0
                    )
            ORDER BY
                collection.institution_id, collection.id, count_concept.position
                ";

            $stmt = $db->query($sql);

            $provider = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                //array_push($row, preg_replace('/\s+/', ' ', $sql));
                array_push($provider, $row);
            }
            return json_encode($provider, JSON_PRETTY_PRINT);
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * front controller with static routing
     *
     * @param $route
     */
    function frontController($route) {
        switch ($route) {
            case 'getMessages':
                echo $this->loadMessages();
                exit;

            case 'getCurrentRecords':
                $url = filter_input(INPUT_GET, 'url');
                $filter = filter_input(INPUT_GET, 'filter');
                $providerId = filter_input(INPUT_GET, 'idProvider');
                $nocache = intval(filter_input(INPUT_GET, 'nocache'));
                echo $this->getCurrentRecords($url, $filter, $providerId, $nocache);
                exit;

            case 'getCountConcepts':
                $providerId = filter_input(INPUT_GET, 'idProvider');
                $url = filter_input(INPUT_GET, 'url');
                $concept = filter_input(INPUT_GET, 'concept');
                $specifier = filter_input(INPUT_GET, 'specifier');
                $filter = filter_input(INPUT_GET, 'filter');
                $nocache = intval(filter_input(INPUT_GET, 'nocache'));
                echo $this->getCountConcepts($providerId, $url, $concept, $specifier, $filter, $nocache);
                exit;

            case 'getCitation':
                $providerId = filter_input(INPUT_GET, 'idProvider');
                $url = filter_input(INPUT_GET, 'url');
                $filter = filter_input(INPUT_GET, 'filter');
                $cached = intval(filter_input(INPUT_GET, 'cached'));
                echo $this->getCitation($providerId, $url, $filter, $cached);
                exit;

            case 'getConcepts':
                $providerId = filter_input(INPUT_GET, 'idProvider');
                echo $this->getConcepts($providerId);
                exit;

            case 'getMaxCalls':
                $providerId = filter_input(INPUT_GET, 'idProvider');
                echo $this->getMaxCalls($providerId);
                exit;

            case 'getTotalMaxCalls':
                echo $this->getTotalMaxCalls();
                exit;

            case 'getProviderMainData':
                $providerId = filter_input(INPUT_GET, 'idProvider');
                echo $this->getProviderMainData($providerId);
                exit;

            default:
// display start page
//                $content_type = "text/html";
//                header('Content-type: ' . $content_type . ', charset=utf-8');
        }
    }

    /**
     * sluggify
     *
     * @param string $str string to be sluggified
     * @return string $sluggified string
     */
    private function sluggify($str) {
        $clean = $str;
        $clean = preg_replace("/[^a-zA-Z0-9\/_| -\.]/", '', $clean);
        $clean = preg_replace("/[\/_| -\.]+/", '-', $clean);
        return strtolower(trim($clean, '-'));
    }

    /**
     * get basic infos of given provider
     *
     * @param int $idProvider
     * @return array
     */
    private function getProviderBasicInfos($idProvider) {
        global $db;
        try {
            $sql = "SELECT
                    institution.id as providerId,
                    institution.name, institution.shortname, institution.url as providerUrl,
                    institution.pywrapper
                    FROM
                    institution
                    WHERE
                    institution.id = '$idProvider'
                    ";

            $stmt = $db->query($sql);

            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                return $row;
            }
            return json_encode($provider, JSON_PRETTY_PRINT);
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * get IDs of providers
     *
     * @return array
     */
    function getProviders() {
        global $db;
        try {
            $sql = "SELECT id FROM institution where active = '1'";
            $stmt = $db->query($sql);
            $result = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $result[] = $row["id"];
            }
            return $result;
        } catch (\PDOException $e) {
            return array();
        }
    }

    /**
     * loads system messages
     */
    function loadMessages() {
        global $db;
        try {
            $sql = "SELECT * FROM message";
            $stmt = $db->query($sql);
            $result = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $result[$row["short"]] = $row["long"];
            }
            $this->message = $result;
            return json_encode($result);
        } catch (\PDOException $e) {
            return json_encode(array($e->getMessage()));
        }
    }

}

///////////////////////////////////////
///////////////////////////////////////


$myBms = new Bms();

$myBms->debugmode = (isset($_GET["debug"]) ? $_GET["debug"] : DEBUGMODE);
$myBms->loadMessages();


/**
 * GET parameter for static routing
 */
$route = filter_input(INPUT_GET, 'action');


$myBms->frontController($route);

//////////////////////////////////////
?><!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
            <title>BioCASe Monitor</title>
            <link rel="stylesheet" type='text/css' href="js/lib/jquery-ui-1.11.4/jquery-ui.min.css"/>
            <link rel="stylesheet" type="text/css" href="css/frame.css"/>
            <link rel="stylesheet" type="text/css" href="css/frontend.css"/>
            <link rel="stylesheet" type="text/css" href="css/custom.css"/>

            <script src="js/lib/jquery-2.1.4.min.js"></script>
            <script src="js/lib/jquery-ui-1.11.4/jquery-ui.min.js"></script>
            <script src="js/general.js"></script>
            <script src="js/frontend.js"></script>

            <?php
            if ($myBms->debugmode == "1") {
                echo '<script src="../js/dev.js"></script>';
                echo '<link rel="stylesheet" type="text/css" href="css/debug.css"/>';
            }
            ?>

    </head>
    <body>
        <div id="topBar">

            <div id="home">
                <figure>
                    <a href="./"><img src="./images/biocase-logo.jpg" height="60" alt="logo" title="BioCASe Monitor Start Page"/></a>
                    <figcaption>Monitor</figcaption>
                </figure>
                <figure>
                    <a href="http://www.gfbio.org" target="_blank"><img src="./images/800px-GFBio_logo_claim_png.png" height="75" alt="GFBIO"/></a>
                    <figcaption></figcaption>
                </figure>
            </div>

            <div id = "menuLinks">

                <div id="adminStatus">
                    <?php
                    echo $_SESSION["fullname"];
                    echo " <a href='core/manageUser.php' title='my profile'><img alt='user avatar' src='../images/glyphicons/glyphicons-4-user.png' height='20'/></a>";
                    ?>

                </div>

                <a href = "./core/admin.php"
                   title = "Administration"
                   ><img alt = "administration"
                      src = "./images/glyphicons/glyphicons-387-log-in.png" height = "20"/></a>
                <a href = "./services/"
                   title = "Webservices"
                   ><img alt = "webservices"
                      src = "./images/RESTful.png"/></a>
                <a id = "footer-control" href = "#"
                   title = "Legal Infos"
                   ><img alt = "info"
                      src = "./images/glyphicons/glyphicons-196-circle-info.png"/></a>

                <div id = "footer">
                    <ul class = "impressum">

                        <li class = "menuItem">
                            <b>BioCASe Monitor</b>
                            <div>
                                v<?php echo _VERSION; ?>
                            </div>
                        </li>

                        <li class="menuItem">
                            <figure>
                                <figcaption>hosted by</figcaption>
                                <a href="http://www.naturkundemuseum.berlin/"
                                   title="http://www.naturkundemuseum.berlin/"
                                   target="_blank">
                                    <img src="./images/mfnlogo_167_190.jpg"
                                         height="30"
                                         alt="Museum f&uuml;r Naturkunde, Berlin"/></a>

                            </figure>
                        </li>

                        <li class="menuItem">
                            <a href="http://biocasemonitor.biodiv.naturkundemuseum-berlin.de/index.php/Documentation"
                               target="_blank">Documentation</a>
                        </li>

                        <li class="menuItem">
                            <a href="./info/impressum.php"
                               target="_blank">Imprint</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mainMenu">
                <div id="menuInfo">Overview</div>
                <div id="progressbar"
                     title="progress meter - number of calls made to BioCASe Provider Software Installations">
                    <div class="progress-label">Loading...</div>
                </div>
                <div id="still-loading" style="display:none;float:right;margin:10px;"></div>

                <ul></ul>
            </div>

        </div>



        <div id="global-link-categories"></div>
        <div id="all-filters"></div>

        <!-- The following DIVs contain info used by javascript functions -->
        <div id="cardAllCalls" style="display:none"><?php echo $myBms->getTotalMaxCalls(); ?></div>

        <?php
        foreach ($myBms->getProviders() as $key => $val) {
            echo "\n\t\t<div id='maxProviderCalls_" . $val . "' style='display:none;'>";
            echo $myBms->getMaxCalls($val);
            echo "</div>";
        }
        ?>

        <div id="SumCalls" style="display:none">
            <?php
            $sum = 0;
            foreach ($myBms->getProviders() as $key => $val) {
                $sum += $myBms->getMaxCalls($val);
            }
            echo $sum;
            ?>
        </div>

        <div id="system-message"></div>

        <div id="main"></div>

    </body>
</html>
