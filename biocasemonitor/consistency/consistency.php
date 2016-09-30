<?php
/**
 * BioCASe Monitor 2.0
 * @copyright  (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/consistency/consistency.php
 * @brief check consistency
 *
 * params: provider, dsa, filter, mapping
 * example:
 *  filter: <filter><like path="/DataSets/DataSet/Metadata/Description/Representation/Title">EDIT - ATBI in Spreewald (Germany)</like></filter>
 *  mapping: ABCD->PanSimple (unused)
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
session_start();
require_once("../config/config.php");

$debugmode = (isset($_GET["debug"]) ? $_GET["debug"] : DEBUGMODE);
$verbose = (isset($_GET["verbose"]) ? $_GET["verbose"] : VERBOSE);

$idProvider = $_GET["provider"];
$dsa = $_GET["dsa"];
$filter = $_GET["filter"];

$mapping_selectbox = "<select><option>--</option></select>";
$default_mapping = (isset($_GET["mapping"]) ? $_GET["mapping"] : "pansimple");

//////////////////////
// get Abcd Infos
try {
    $sql = "SELECT abcd_concept.abcd, abcd_concept.status, abcd_concept.rule FROM abcd_concept";
    $stmt = $db->query($sql);

    $abcd = array();

    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $abcd[$row["abcd"]] = $row;
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
?><!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>BioCASe Monitor - Consistency Check</title>
        <link rel="stylesheet" type='text/css' href="../js/lib/jquery-ui-1.11.4/jquery-ui.min.css"/>
        <link rel="stylesheet" type="text/css" href="../css/frame.css"/>
        <link rel="stylesheet" type="text/css" href="../css/frontend.css"/>
        <link rel="stylesheet" type="text/css" href="../css/custom.css"/>
        <link rel="stylesheet" type="text/css" href="../css/consistency.css"/>

        <script src="../js/lib/jquery-2.1.4.min.js"></script>
        <script src="../js/lib/jquery-ui-1.11.4/jquery-ui.min.js"></script>

        <script src="../js/general.js"></script>
        <script src="../js/consistency.js"></script>

        <?php
        if ($debugmode == "1") {
            echo '<link rel="stylesheet" type="text/css" href="../css/debug.css"/>';
            echo '<script src="../js/dev.js"></script>';
        }
        ?>

        <script>
            //abcd = '<?php echo json_encode($abcd, JSON_FORCE_OBJECT); ?>';
            abcd = '<?php echo json_encode($abcd); ?>';
            abcd = JSON.parse(abcd);
            idProvider = "<?php echo $idProvider ?>";
            dsa = "<?php echo $dsa ?>";
            filter = '<?php echo $filter ?>';
            verbose = '<?php echo $verbose ?>';

            console.log("verbose=" + verbose);
            console.log("provider=" + idProvider);
            console.log("dsa=" + dsa);
            console.log("filter=" + filter);
        </script>
    </head>
    <body>

        <?php
        $page_title = "<div>Consistency check: ";
        $page_title .= $dsa;
        $page_title .= "<table>";
        $page_title .= "<tr><td>filter: <td>" . str_replace("<", "&lt;", $filter) . "</td></tr>";
        $page_title .= "<tr><td>mappping: <td>" . $mapping_selectbox . "</td></tr>";
        $page_title .= "</table>";
        $page_title .= "</div>";
        ?>

        <div id="topBar">

            <div id="home">
                <figure>
                    <a href="../"><img src="../images/biocase-logo.jpg" height="60" alt="logo" title="BioCASe Monitor Start Page"/></a>
                    <figcaption>Monitor</figcaption>
                </figure>
                <figure>
                    <a href="http://www.gfbio.org" target="_blank"><img src="../images/800px-GFBio_logo_claim_png.png" height="75" alt="GFBIO"/></a>
                    <figcaption></figcaption>
                </figure>
            </div>

            <div id = "menuLinks">

                <div id="adminStatus">
                    <?php
                    if (!$_SESSION["authenticated"]) {
                        echo "guest";
                    } else {
                        echo $_SESSION["fullname"];
                        echo " <a href='../core/manageUser.php' title='my profile'><img alt='user avatar' src='../images/glyphicons/glyphicons-4-user.png' height='20'/></a>";
                    }
                    ?>
                </div>

                <a href = "../core/admin.php"
                   title = "Administration"
                   ><img alt = "administration"
                      src = "../images/glyphicons/glyphicons-387-log-in.png" height = "20"/></a>
                <a href = "../services/"
                   title = "Webservices"
                   ><img alt = "webservices"
                      src = "../images/RESTful.png"/></a>
                <a id = "footer-control" href = "#"
                   title = "Legal Infos"
                   ><img alt = "info"
                      src = "../images/glyphicons/glyphicons-196-circle-info.png"/></a>

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
                                    <img src="../images/mfnlogo_167_190.jpg"
                                         height="30"
                                         alt="Museum f&uuml;r Naturkunde, Berlin"/></a>

                            </figure>
                        </li>

                        <li class="menuItem">
                            <a href="http://biocasemonitor.biodiv.naturkundemuseum-berlin.de/index.php/Documentation"
                               target="_blank">Documentation</a>
                        </li>

                        <li class="menuItem">
                            <a href="../info/impressum.php"
                               target="_blank">Imprint</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mainMenu">
                <div id="menuInfo">Overview</div>

                <ul></ul>
            </div>

        </div>

        <div id="abcd"></div>


        <div id="progressbar"
             title="progress meter - number of requests made to BioCASe Provider Software Installation">
            <div class="progress-label"></div>
        </div>
        <div id="max-calls" style="display:none;"></div>

        <div id="dsa"></div>

        <table id="consistency" >
            <tr>
                <th></th>
                <th>ABCD concept</th>
                <th>searchable</th>
                <th>datatype</th>
                <th>status
                    <div style="font-weight:normal; font-size:0.5rem;">
                        M&nbsp;(mandatory)
                        <br>H&nbsp;(highly&nbsp;recommended)
                        <br/>R&nbsp;(recommended)
                    </div>
                </th>
                <th>target element</th>
                <th>example values</th>
                <th>counters</th>
                <th>check all values</th>
            </tr>
        </table>
        <div id="all-filters"></div>

        <script>
            $(document).ready(function () {

                // set Title
                $("#menuInfo").html('<?php echo $page_title; ?>');


            });
        </script>
    </body>
</html>
