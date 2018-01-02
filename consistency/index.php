<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @namespace Consistency
 * @file biocasemonitor/consistency/index.php
 * @brief check consistency
 * @todo: call webservice via AJAX: "../services/providers/index.php"

 * params: provider, dsa, filter, source_schema, target_schema, mapping
 * example:
 *  provider: MfN
 *  dsa: mfn_pal
 *  filter: <filter><like path="/DataSets/DataSet/Metadata/Description/Representation/Title">EDIT - ATBI in Spreewald (Germany)</like></filter>
 *  source_schema: ABCD2.06
 *  target_schema: pansimple
 *  mapping: abcd_pansimple
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

namespace Consistency;

session_start();
require_once("../config/config.php");

$debugmode = (isset($_GET["debug"]) ? $_GET["debug"] : DEBUGMODE);
$verbose = (isset($_GET["verbose"]) ? $_GET["verbose"] : VERBOSE);

$custom_layout = (isset($_GET["custom"]) ? $_GET["custom"] : 1);

$idProvider = $_GET["provider"];
$dsa = $_GET["dsa"];
$filter = $_GET["filter"];
$mapping = $_GET["mapping"];

$source_schema = $_GET["source_schema"];
$target_schema = $_GET["target_schema"];

$default_target = (isset($_GET["target-schema"]) ? $_GET["target-schema"] : "pansimple");
$default_mapping = "abcd_pansimple";



////////////////
// get Provider
//
// @todo: call webservice via AJAX: "../services/providers/index.php");

try {
    $sql = "SELECT
                    institution.id,
                    institution.shortname,
                    institution.name,
                    institution.url,
                    institution.pywrapper as biocase_url
                FROM institution
                WHERE active = '1'
                AND (institution.id LIKE :id OR institution.shortname LIKE :id)";

    $values = array();
    $values[":id"] = $idProvider;

    $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $stmt->execute($values);

    $result = array();
    if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $result = $row;
    }
    $providerShortname = $result["shortname"];
    $biocaseUrl = $result["biocase_url"];
} catch (\PDOException $e) {
    $providerShortname = $idProvider;
}


try {
    $sql = "SELECT
        schema_mapping.name as mapping,
        source.urn as source_schema_urn,
        target.urn as target_schema_urn
    FROM schema_mapping
    INNER JOIN schema           ON schema.shortname = schema_mapping.source_schema
    INNER JOIN schema AS source ON schema_mapping.source_schema = source.shortname
    INNER JOIN schema AS target ON schema_mapping.target_schema = target.shortname ";

//        if ($mapping)
//            $sql .= " WHERE 1  AND schema_mapping.name='" . $mapping . "'";
//    $sql .= " ORDER BY schema_mapping.name";
    $sql .= " ORDER BY schema_mapping.id";

    $stmt = $db->query($sql);


    $mapping_list = array();
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $mapping_list[] = $row;
    }
} catch (\PDOException $e) {
    //echo $e->getMessage();
}
?><!doctype html>
<html lang="en">
    <head>
        <title>BioCASe Monitor - Consistency Check</title>

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>

        <link rel="stylesheet" type="text/css" href="../css/general.css"/>
        <link rel="stylesheet" type="text/css" href="../css/frontend.css"/>
        <link rel="stylesheet" type="text/css" href="../css/consistency.css"/>

        <script src="../js/lib/jquery-2.1.4.min.js"></script>

        <link rel="stylesheet" type='text/css' href="../js/lib/bootstrap-3.3.7/css/bootstrap.min.css"/>
        <script src="../js/lib/bootstrap-3.3.7/js/bootstrap.js"></script>

        <link rel="stylesheet" type="text/css" href="../js/lib/DataTables/datatables.min.css">
        <script type="text/javascript" charset="utf8" src="../js/lib/DataTables/datatables.min.js"></script>

        <script src="../js/general.js"></script>
        <script src="../js/consistency.js"></script>

        <?php
        if ($debugmode == "1") {
            echo '<link rel="stylesheet" type="text/css" href="../css/debug.css"/>';
            echo '<script src="../js/dev.js"></script>';
        }
        if (CUSTOM == "1") {
            echo '<link rel="stylesheet" type="text/css" href="../css/custom.css"/>';
            echo '<script src="../js/custom.js"></script>';
        }
        ?>

        <script>
            queryUrl = "<?php echo $biocaseUrl ?>" + biocaseResponseUrl + "<?php echo $dsa ?>";
            sourceSchema = "";
            currentProgress = 0;
        </script>

    </head>
    <body>
        <?php
        include_once("../config/custom/analyticstracking.php");
        include("navbar.php");
        ?>

        <div class="container" style="width:98%;margin-left:1%;margin-right:1%;">
            <div class="row">
                <div class="col-md-3 alert alert-warning">
                    <form method="get">
                        <h3>Data Source</h3>
                        <table width="100%">
                            <tr><td>provider:</td> <td><input name="provider" value="<?php echo $providerShortname ?>"/></td></tr>
                            <tr><td>data source:</td> <td><input name="dsa" value="<?php echo $dsa ?>"/></td></tr>
                            <tr><td>filter:</td> <td><input name="filter" value="<?php echo strip_tags($filter) ?>"/></td></tr>
                            <tr><td>mapping:</td>
                                <td> <select id='mapping' name='mapping' onchange="submit()">
                                        <option>---</option>
                                        <?php
                                        foreach ($mapping_list as $row) {
                                            echo "<option ";
                                            if ($row["mapping"] == $mapping) {
                                                echo " selected='selected'";
                                            }
                                            echo ">" . $row["mapping"] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td></tr>
                        </table>
                        <div style="float:right"><input type="submit" value="go !"/></div>

                    </form>
                    <hr/>
                    <h5>supported schemas:</h5>
                    <div id='supported-schemas'></div>

                </div>

                <div class="col-md-9 alert alert-info">
                    <h3>Summary</h3>
                    <div class="progress">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">0 %</span>
                        </div>
                    </div>
                    <table id="infoline" >
                        <thead>
                            <tr>
                                <th>#elements</th>
                                <th>errors</th>
                                <th>warnings</th>
                                <th>infos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div id="nb-total" title="mapped concepts" style="font-weight:bold"></div>
                                    <div id="nb-searchable" title="searchable concepts"></div>
                                    <div id="nb-notsearchable" title="not-searchable concepts"></div>
                                    <div id="nb-mapped" title="mapped concepts"></div>
                                    <div id="nb-mapped-with-rules" title="mapped concepts having rules"></div>
                                    <div id="nb-capabilities" title="capabilities"></div>
                                </td>
                                <td class="error" ><div class="cardinal"></div></td>
                                <td class="warning"><div class="cardinal"></td>
                                <td class="info"><div class="cardinal"></td>
                            </tr>
                            <tr>
                                <td><div id="nb-missing-mandatory" title="missing mandatory elements"></div></td>
                                <td colspan="3"><div id="missing-mandatory"></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


            <div id="debuginfo"></div>
                                    
            <div class="row">
                <div class="col-md-12">
                    <table id="consistency" class="table table-bordered table-hover table-condensed table-responsive  table-striped" >
                        <thead>
                            <tr>
                                <th></th>
                                <th>source&nbsp;element</th>
                                <th>more infos</th>
                                <th>searchable</th>
                                <th>datatype</th>
                                <th>target&nbsp;element</th>
                                <th>tags<a data-toggle="tooltip" class="glyphicon glyphicon-info-sign"
                                           title="gfBio-context: M&nbsp;(mandatory) H&nbsp;(highly&nbsp;recommended) R&nbsp;(recommended) U&nbsp;(unique)"></a></th>
                                <th>rules</th>
                                <th>counters</th>
                                <th>example values</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfooter>
                            <tr>
                                <th></th>
                                <th>source&nbsp;element</th>
                                <th>more info</th>
                                <th>searchable</th>
                                <th>datatype</th>
                                <th>target&nbsp;element</th>
                                <th>tags<a data-toggle="tooltip" class="glyphicon glyphicon-info-sign"
                                           title="gfBio-context: M&nbsp;(mandatory) H&nbsp;(highly&nbsp;recommended) R&nbsp;(recommended) U&nbsp;(unique)"></a></th>
                                <th>rules</th>
                                <th>counters</th>
                                <th>example values</th>
                            </tr>
                        </tfooter
                    </table>
                </div>
            </div>

        </div>

        <div id="all-filters"></div>
        <div id="dsa"></div>

    </body>
</html>
