<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
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

require_once("config/config.php");

session_start();
if (!$_SESSION) {
    $_SESSION['authenticated'] = 0;
    $_SESSION['rights'] = 0;
    $_SESSION['provider'] = -1;

    $_SESSION["username"] = "guest";
    $_SESSION["fullname"] = "Guest";
    $_SESSION["email"] = "";
}

include("./lib/util.php");

$providerId = $_REQUEST["provider"];
$url = $_REQUEST["file"];
$filter = $_REQUEST["filter"];
$schema = $_REQUEST["schema"];

if (!$schema)
    $schema = DEFAULT_SCHEMA;

$concept = "/DataSets/DataSet/Units/Unit/MultiMediaObjects/MultiMediaObject/FileURI";


////////////////////////////
// CACHE
//

$provider_basics = getProviderBasicInfos($providerId);

$cache_dir = "./" . CACHE_DIRECTORY . strtolower($provider_basics["shortname"]);
@mkdir($cache_dir);

$cache_subdir = strtolower(end(explode("=", parse_url($url, PHP_URL_QUERY))));
@mkdir($cache_dir . "/" . $cache_subdir);

if ($filter) {
    $like_string = "<?xml version='1.0' standalone='yes'?>" . PHP_EOL . $filter;
    $like_xpath = new \SimpleXMLElement($like_string);
    $like_element = $like_xpath->xpath('/like')[0];
    $cache_filterdir = sluggify($like_element);
} else {
    $cache_filterdir = "";
}
@mkdir($cache_dir . "/" . $cache_subdir . "/" . $cache_filterdir);

$cachefile = $cache_dir . "/" . $cache_subdir . "/" . $cache_filterdir . "/landingpage.xml";


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
//$arg[] = "schema=http://www.tdwg.org/schemas/abcd/2.06";
$arg[] = "schema=" . $schema;
$arg[] = "wrapper_url=" . $url;
//$arg[] = "inst=";
//$arg[] = "cat=";
//$arg[] = "coll=";
$unit_url = $path_parts["dirname"] . "/querytool/details.cgi?" . implode("&", $arg);

// querytool url
$querytool_url = $path_parts["dirname"] . "/querytool/main.cgi?" . implode("&", $arg);

///////////////////////////////////////////////////////////
// check if predefined concept is part of the capabilities
//
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_REFERER, "http://www.naturkundemuseum.berlin");
curl_setopt($ch, CURLOPT_HEADER, false);
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


if ($has_multimedia === false) {
    // filter remains as given by request-parameter
    $xsl_source = "./lib/templates/landingpage_no_multimedia.xsl";
} else {
    /////////////////////////////
    // check if there exist multimedia records
    //

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
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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
        $xsl_source = "./lib/templates/landingpage_no_multimedia.xsl";
    } else {
        $filter = '<and><isNotNull path="' . $concept . '"></isNotNull>' . $filter . '</and>';
        $xsl_source = "./lib/templates/landingpage.xsl";
    }

    $caching_info .= "\n<br>has records with non-empty FileURI: " . $cardinal . "";
}


$caching_info .= "\n\n<br>filter: <br/><textarea cols='120' rows='5'>" . $filter . "</textarea>";
$caching_info .= "\n<br>xsl: " . $xsl_source;


if (file_exists($cachefile) && filesize($cachefile) && (time() - CACHING_INTERVAL < filemtime($cachefile))) {
    $xml_string = file_get_contents($cachefile);
    $caching_info .= "\n<br>read from cache " . date(' jS F Y H:i ', filemtime($cachefile)) . " --- " . round(filesize($cachefile) / 1024) . "KB";
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
    curl_setopt($ch, CURLOPT_POST, true);
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
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "query=" . urlencode($request));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $xml_string = curl_exec($ch);
        curl_close($ch);

        $out = file_put_contents($cachefile, $xml_string);
        $caching_info .= "\n<br>cachefile created: " . ($out ? $out . " bytes" : "***error***");
    } else {
        $errorpage = <<< ERRORPAGE
<!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <title>Dataset Landingpage - Error</title>
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
        <title>Dataset Landingpage</title>
        <link rel="stylesheet" type='text/css' href="js/lib/bootstrap-3.3.7/css/bootstrap.min.css"/>
        <link rel="stylesheet" type="text/css" href="css/general.css"/>
        <link rel="stylesheet" type="text/css" href="css/frontend.css"/>
        <link rel="stylesheet" type="text/css" href="css/custom.css"/>
        <script src="js/lib/jquery-2.1.4.min.js"></script>
        <script src="js/lib/bootstrap-3.3.7/js/bootstrap.js"></script>
        <script src="js/general.js"></script>
    </head>
    <body>

        <?php
        $begin_content = strpos($xml_string, "<biocase:content");
        $begin_diagnostics = strpos($xml_string, "<biocase:diagnostics");
        $diagnostic = substr($xml_string, $begin_diagnostics);

        if (strpos($diagnostic, "ERROR") > 0) {
            echo "<h1>Landingpage</h1>";
            echo "<h2>" . $_GET["file"] . "</h2>";
            echo "<h3>ERROR</h3>";
            echo "<textarea style='width:80%;height:200px;color:darkred;'>" . $diagnostic . "</textarea>";
            exit;
        }
        ?>

        <script type="text/javascript">
            dsa = '<?php echo $dsa; ?>';
            originalURL = '<?php echo $url; ?>';
            filterRequest = '<?php echo $filter; ?>';
            unitUrl = '<?php echo $unit_url; ?>';
            querytoolUrl = '<?php echo $querytool_url; ?>';
            cacheFile = '<?php echo $cachefile; ?>';
        </script>

        <script type="text/javascript" src="./lib/Saxonce/Saxonce.nocache.js"></script>
        <script type="application/xslt+xml"
                language="xslt2.0"
                src="<?php echo $xsl_source; ?>"
                data-source="<?php echo $cachefile; ?>"
                >
        </script>


        <nav class="navbar  navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <figure>
                        <a href="./">
                            <img src="./images/biocase-logo.jpg" alt="logo" title="BioCASe Monitor Start Page"/>
                        </a>
                        <figcaption>Monitor</figcaption>
                    </figure>
                </div>
                <div class="navbar-header">
                    <?php
                    if (CUSTOM == "1") {
                        include "config/custom/customize.php";
                    }
                    ?>
                </div>

                <div class="navbar-header">
                    <h3>ABCD Landingpage</h3>
                </div>

                <ul class="nav navbar-nav navbar-left">
                    <?php if ($_SESSION["authenticated"]) { ?>
                        <li>
                            <a href="admin/manageProvider.php"
                               id="menuProvider"
                               title="manage provider metadata"
                               class="glyphicon glyphicon-cog"> Dashboard</a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>

                <ul class="nav navbar-nav navbar-right">


                    <?php if (!$_SESSION["authenticated"]) { ?>
                        <li>
                            <a href="admin/index.php" title="Administration" class="glyphicon glyphicon-log-in"> Login</a>
                        </li>

                        <?php
                    } else {
                        echo "<li>
                                    <a href='admin/manageUser.php' title='profile' class='glyphicon glyphicon-user'> " . $_SESSION["fullname"] . "</a>
                                </li>";
                        echo "<li>
                                    <a href='index.php?log_out=1' title='log out' class='glyphicon glyphicon-log-out'> Logout</a>
                                </li>";
                    }
                    ?>

                    <li>
                        <a href="./services/" title="API" class="glyphicon glyphicon-globe"> Webservices</a>
                    </li>

                    <li>
                        <a id="footer-control" href="#"
                           title="Legal Infos"
                           class="glyphicon glyphicon-info-sign"> Legal</a>
                    </li>
                </ul>
            </div>

        </nav>

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



        <div class="container">

            <div class="row">
                <div class="progress">
                    <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width:100%">
                        Please be patient, the landingpage is being generated.
                    </div>
                </div>

                <div>
                    <div class="alert alert-warning">
                        In the unlikely event that the landingpage will not be displayed,
                        the following infos might help to find the reason.
                    </div>
                    <table class="table">
                        <tr><td>Query Url:</td><td><a target="landingpage" href="<?php echo $url; ?>"><?php echo $url; ?></a></tr>
                        <tr><td>Querytool Url:</td><td> <a target="landingpage" href="<?php echo $querytool_url; ?>"><?php echo $querytool_url; ?></a></tr>
                        <tr><td>Unit Url:</td><td> <a target="landingpage" href="<?php echo $unit_url; ?>"><?php echo $unit_url; ?></a></tr>
                        <tr><td>Cache Url:</td><td> <a target="landingpage" href="<?php echo $cachefile; ?>"><?php echo $cachefile; ?></a></tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <h2>Diagnostics</h2>
                <textarea cols="120" rows="20" style="width:100%"
                          ><?php
                              echo substr($xml_string, 0, $begin_content);
                              echo "<biocase:content>\n\t not displayed\n" . "</biocase:content>\n";
                              echo $diagnostic;
                              ?></textarea>
            </div>
        </div>
    </body>
</html>
