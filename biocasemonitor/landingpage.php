<?php
/**
 * BioCASe Monitor 2.0
 * @copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/landingpage.php
 * @brief landing page for ABCD2.06 raw xml
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

//header('Content-type: text/html, charset=utf-8');

require_once("./config/config.php");
include("./lib/util.php");

$providerId = $_REQUEST["provider"];
$url = $_REQUEST["file"];
$filter = $_REQUEST["filter"];
$concept = "/DataSets/DataSet/Units/Unit/MultiMediaObjects/MultiMediaObject/FileURI";


////////////////////////////
// CACHE
//

$provider_basics = getProviderBasicInfos($providerId);
$cache_path = parse_url($url, PHP_URL_QUERY);

$cache_dir = "./" . CACHE_DIRECTORY . strtolower($provider_basics["shortname"]);
@mkdir($cache_dir);
$cachefile = $cache_dir . "/"
        . strtolower(preg_replace("/dsa=/", 'dsa+', $cache_path))
        . "--" . sluggify(strip_tags($_REQUEST["filter"]))
        . "--landingpage.xml";

$caching_info = "";
$caching_info .= "\n<br>concept: " . $concept;
$caching_info .= "\n<br>cachefile: " . $cachefile;



// unit url
$path_parts = pathinfo($url);
$atmp = explode("?", $path_parts["basename"]);
$dsa = $atmp[1];
$arg = array();
$arg[] = $dsa;
$arg[] = "detail=unit";
$arg[] = "schema=http://www.tdwg.org/schemas/abcd/2.06";
$arg[] = "wrapper_url=" . $url;
//$arg[] = "inst=";
//$arg[] = "cat=";
//$arg[] = "coll=";
$unit_url = $path_parts["dirname"] . "/querytool/details.cgi?" . implode("&", $arg);



///////////////////////////////////////////////////////////
// check if predefined concept is part of the capabilities
//
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_REFERER, "http://www.naturkundemuseum.berlin");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$xml_string = curl_exec($ch);
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
        <xsl:apply-templates select="//biocase:capabilities"/>
    </xsl:template>

    <xsl:template match="//biocase:capabilities">
        <xsl:value-of select="."/>
    </xsl:template>

</xsl:stylesheet>';

$xslt = new \XSLTProcessor();
$xslt->importStylesheet(new \SimpleXMLElement($xsltString));


$capabilities = $xslt->transformToXml(new \SimpleXMLElement($xml_string));
$has_multimedia = strpos($capabilities, $concept);

$caching_info .= "\n<br>has FileURI capability. ";
//. $has_multimedia . " " . substr($capabilities, $has_multimedia, strlen($concept));



if ($has_multimedia === false) {
    // filter remains as given by request-parameter
    $xsl_source = "../lib/templates/landingpage_no_multimedia.xsl";
} else {
    /////////////////////////////
    // check if there exist multimedia records
    //
    //$caching_info .= "\n<br>check if there exist multimedia records... ";

    $request = '<?xml version="1.0" encoding="UTF-8"?>
    <request xmlns="http://www.biocase.org/schemas/protocol/1.3">
      <header><type>search</type></header>
      <search>
            <requestFormat>http://www.tdwg.org/schemas/abcd/2.06</requestFormat>
            <responseFormat start="0" limit="20000">http://www.tdwg.org/schemas/abcd/2.06</responseFormat>
            <filter>
                <and><isNotNull path="' . $concept . '"></isNotNull>' . $filter . '</and>
            </filter>
            <count>true</count>
      </search>
    </request>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.naturkundemuseum.berlin");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $xml_string = curl_exec($ch);
    curl_close($ch);

    // XSLT
    $xsltString = '<?xml version="1.0" encoding="UTF-8"?>
    <xsl:stylesheet version="1.0"
            xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
            xmlns:biocase="http://www.biocase.org/schemas/protocol/1.3"
            xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06"
    >
        <xsl:output method="text" omit-xml-declaration="yes"/>
        <xsl:template match="/">
            <xsl:value-of select="//biocase:count"/>
        </xsl:template>
    </xsl:stylesheet>';

    $xslt = new \XSLTProcessor();
    $xslt->importStylesheet(new \SimpleXMLElement($xsltString));
    $cardinal = $xslt->transformToXml(new \SimpleXMLElement($xml_string));

    if (intval($cardinal) === 0) {
        $xsl_source = "../lib/templates/landingpage_no_multimedia.xsl";
    } else {
        $filter = '<and><isNotNull path="' . $concept . '"></isNotNull>' . $_REQUEST["filter"] . '</and>';
        $xsl_source = "../lib/templates/landingpage.xsl";
    }

    $caching_info .= "\n<br>has records with non-empty FileURI: " . $cardinal . "";
}


$caching_info .= "\n\n<br>filter: <br/><textarea cols='120' rows='5'>" . $filter . "</textarea>";
$caching_info .= "\n<br>xsl: " . $xsl_source;


if (file_exists($cachefile) && filesize($cachefile) && (time() - CACHING_INTERVAL < filemtime($cachefile))) {
    $xml_string = file_get_contents($cachefile);
    $caching_info .= "\n<br>read from cache " . date(' jS F Y H:i ', filemtime($cachefile)) . " --- " . round(filesize($cachefile) / 1024) . "KB";
    //$caching_info .= "\n<br><textarea cols=200 rows=20>" . $xml_string . "</textarea>";
} else {
    /////////////////////////////////////
    // CURL
    //
    $caching_info .= "\n<br>cache not found or not valid any more ";
    $caching_info .= "\n<br>making a new cURL request ";

    // build request
    $request = '<?xml version="1.0" encoding="UTF-8"?>
    <request xmlns="http://www.biocase.org/schemas/protocol/1.3">
      <header><type>search</type></header>
      <search>
            <requestFormat>http://www.tdwg.org/schemas/abcd/2.06</requestFormat>
            <responseFormat start="0" limit="200">http://www.tdwg.org/schemas/abcd/2.06</responseFormat>
            <filter>' . $filter . '</filter>
            <count>false</count>
      </search>
    </request>';

    $caching_info .= "\n<br>request: <textarea cols=100>" . $request . "</textarea>";

    // FIRST GET ONLY HEADERS
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
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

        $out = file_put_contents($cachefile, $xml_string);
        $caching_info .= "\n<br>cachefile created: " . ($out ? $out . " bytes" : "***error***");
        // @todo: an option is to add to the cachefile relevant data such as URLs for the Local Query Tool
    } else {
        $errorpage = <<< ERRORPAGE
<!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <title>ABCD Dataset Landingpage - Error</title>
        </head>
        <body>
            <h1>Requested service from $url currently not available.</h1>
        </body>
    </html>
ERRORPAGE;
        echo $errorpage;
        exit;
    }
}
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>ABCD Dataset Landingpage</title>
        <script src="../js/lib/jquery-2.1.4.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/frame.css"/>
        <link rel="stylesheet" type="text/css" href="css/frontend.css"/>
        <link rel="stylesheet" type="text/css" href="css/custom.css"/>
        <style>
            fieldset{padding:10px; margin:10px;}
            fieldset div {display: none;}
        </style>
    </head>
    <body>

        <?php
        $begin_content = strpos($xml_string, "<biocase:content");
        $begin_diagnostics = strpos($xml_string, "<biocase:diagnostics");
        $diagnostic = substr($xml_string, $begin_diagnostics);

        if (strpos($diagnostic, "ERROR") > 0) {
            echo "<h1>ABCD Landingpage</h1>";
            echo "<h2>" . $_GET["file"] . "</h2>";
            echo "<h3>ERROR</h3>";
            echo "<textarea style='width:80%;height:200px;color:darkred;'>" . $diagnostic . "</textarea>";
            exit;
        }
        ?>

        <script type="text/javascript">
            var dsa = '<?php echo $dsa; ?>';
            var originalURL = '<?php echo $url; ?>';
            var filterRequest = '<?php echo $filter; ?>';
            var unitUrl = '<?php echo $unit_url; ?>';
            var cacheFile = '<?php echo $cachefile; ?>';
        </script>

        <script type="text/javascript" src="../lib/Saxonce/Saxonce.nocache.js"></script>
        <script type="application/xslt+xml"
                language="xslt2.0"
                src="<?php echo $xsl_source; ?>"
                data-source="<?php echo $cachefile; ?>"
                >
        </script>

        <!-- default output -->

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


            <div class="mainMenu">
                <div id="menuInfo">Dataset Landingpage</div>
            </div>


        </div>


        <fieldset>
            <legend>General Infos</legend>

            <b>You are seeing this info page because, for some reason, the HTML Landingpage could not be displayed.</b>
            <br/><br/>

            DataSource Url: <a target="landingpage" href="<?php echo $url; ?>"><?php echo $url; ?></a>
            <br/><br/>
            Unit Url: <a target="landingpage" href="<?php echo $unit_url; ?>"><?php echo $unit_url; ?></a>
            <br/><br/>
            Cache Url: <a target="landingpage" href="<?php echo $cachefile; ?>"><?php echo $cachefile; ?></a>
            <br/><br/>

            <div id="url" ><?php echo $url; ?></div>
            <div id="unitUrl"><?php echo $unit_url; ?></div>
            <div id="cacheFile"><?php echo $cachefile; ?></div>
            <div id="cacheInfo">
                <?php //echo $caching_info;  ?>
            </div>

        </fieldset>

        <fieldset>
            <legend>Diagnostics</legend>
            <textarea cols="120" rows="20" style="width:100%">
                <?php
                echo substr($xml_string, 0, $begin_content);
                echo "<biocase:content>\n\t not displayed\n"
                . "</biocase:content>\n";
                echo $diagnostic;
                ?>
            </textarea>

        </fieldset>


    </body>
</html>
