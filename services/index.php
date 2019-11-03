<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @namespace Webservices
 * @file biocasemonitor/services/index.php
 * @brief webservices GUI entry point
 *
 * @section LICENSE
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

namespace Webservices;

// no session handling
require_once("../config/config.php");

$alist = explode(",", $_SERVER["HTTP_X_FORWARDED_HOST"]);
$server_name = $alist[0];
if (!$server_name) {
    $server_name = $_SERVER["SERVER_NAME"];
}
$server_url = "http://" . $server_name . dirname($_SERVER['REQUEST_URI']) . "/services/";


header('Content-type: text/html, charset=utf-8');
?><!doctype html><html>
    <head>
        <title>BioCASe Monitor Webservice</title>

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>

        <link rel="stylesheet" type='text/css' href="../js/lib/bootstrap-3.3.7/css/bootstrap.min.css"/>
        <link rel="stylesheet" type='text/css' href="../js/lib/jquery-ui-1.11.4/jquery-ui.min.css"/>

        <link rel="stylesheet" type="text/css" href="../css/general.css"/>
        <link rel="stylesheet" type="text/css" href="../css/frontend.css"/>

        <script src="../js/lib/jquery-2.1.4.min.js"></script>
        <script src="../js/lib/bootstrap-3.3.7/js/bootstrap.js"></script>
        <script src="../js/lib/jquery-ui-1.11.4/jquery-ui.min.js"></script>

        <script src="../js/general.js"></script>

        <?php
        if ($myBms->debugmode == "1") {
            echo '<script src="../js/dev.js"></script>';
            echo '<link rel="stylesheet" type="text/css" href="../css/debug.css"/>';
        }
        if ($custom_layout == "1") {
            echo '<script src="../js/custom.js"></script>';
            echo '<link rel="stylesheet" type="text/css" href="../css/custom.css"/>';
        }
        ?>


        <style>
            * {font-size: 1em;}
            #menuLinks {display:none}
            form {display: inline-block; min-width: 20%; max-width: 30%; }
            input {margin: 5px; min-width:15em; max-width:30em;}
            select {margin: 5px; min-width:15em; max-width:30em;}
            h4 {margin:5px;}
            .direct-call {margin-top:30px;  visibility: hidden;}
            .output {float:right; min-width: 60%; max-width: 80%;}
            pre {
                background-color: ghostwhite;
                border: 1px solid silver;
                padding: 2px 2px;
                margin: 0;
                font-family: monospace !important;
                font-size: 0.7em;
            }
            .json-key {
                color: #3CACE4;
            }
            .json-value {
                color: #85B449;
            }
            .json-string {
                color: #3E66AD;
            }
            #main {
                margin-left:50px;
                margin-top:20px;
            }
            .example {
                font-style: italic;
            }
        </style>

        <?php
        if (DEBUGMODE == 1) {
            echo '<script src="../js/dev.js"></script>';
            echo '<link rel="stylesheet" type="text/css" href="../css/debug.css"/>';
        }
        ?>

    </head>
    <body>

        <?php
        include_once("../config/custom/analyticstracking.php");
        include("./navbar.php");
        ?>

        <div id="main">

            <h1>Webservices</h1>
            <!-- <h3>Forms to fill in</h3> -->

            <div id="service-forms">
                <h3>get Providers</h3>
                <div>
                    <form action="providers/" method="GET" target="webservices">
                        no parameters required
                        <br/>
                        <input name="provider_id" placeholder="provider_id" type="text"/>
                        <br/>
                        <input name="provider_name" placeholder="provider_shortame" type="text"/>
                        <br/>
                        <input type="submit" value="go !"/>
                        <div class="direct-call">
                            <a target="webservices" href="#" title=""> > direct link</a>
                        </div>
                    </form>

                    <div class="output"></div>
                </div>

                <h3>get Datasets</h3>
                <div>
                    <form action="datasets/" method="GET" target="webservices">
                        no parameters required
                        <br/>
                        <input name="provider_id" placeholder="provider_id" type="text"/>
                        <br/>
                        <input name="dataset_id" placeholder="dataset_id" type="text"/>
                        <br/>
                        <input type="submit" value="go !"/>
                        <div class="direct-call">
                            <a target="webservices" href="#" title=""> > direct link</a>
                        </div>
                    </form>

                    <div class="output"></div>
	    	</div>

                <h3>get Capabilities</h3>
                <div>
                    <form action="capabilities/" method="GET" target="webservices" id="capabilities">
                        <input name="format"  type="hidden" value="json"/>
                        required parameters
                        <br/>
                        <input name="provider_id" placeholder="provider_id" type="text" required="required"/>
                        <br/>
                        <input name="dsa" placeholder="dsa" type="text" required="required"/>
                        <!-- <select name="my_dsa" placeholder="dsa"  required="required"></select> -->
                        <br/>
                        <input type="submit" value="go !"/>
                        <div class="direct-call">
                            <a target="webservices" href="#" title=""> > direct link</a>
                        </div>
                    </form>
                    <div class="output"></div>
                </div>

                <h3>get Landingpages</h3>
                <div>
                    <form action="landingpages/" method="GET" target="webservices">
                        <input name="output"  type="hidden" value="json"/>
                        required parameters
                        <br/>
                        <input name="provider_id" placeholder="provider shortname or ID" type="text" required="required"/>
                        <br/>
                        <input name="dsa" placeholder="dsa" type="text" required="required"/>

                        <h4>Data Set</h4>
                        not required
                        <br/>
                        <input name="filter" placeholder="filter" type="text" />


                        <h4>Data Unit</h4>
                        not required
                        <br/>
                        <input name="inst" placeholder="institution" type="text" />
                        <br/>
                        <input name="col" placeholder="collection" type="text" />
                        <br/>
                        <input name="cat" placeholder="cat" type="text" />
                        <br/>
                        <br/>
                        <input type="submit" value="go !"/>

                        <div class="direct-call">
                            <a target="webservices" href="#" title=""> > direct link</a>
                        </div>
                    </form>


                    <div class="output"></div>

                    <fieldset><legend>examples</legend>
                        <div class="example">
                            provider=MfN<br/>
                            provider_id=1<br/>
                            dsa=mfn_PAL<br/>
                            filter=Fossil Invertebrates Ia<br/>
                            inst=MfN<br/>
                            col=MfN - Fossil invertebrates Ia<br/>
                            cat=MB.Ga.3895

                            <hr/>
                            provider=ZFMK<br/>
                            provider_id=3<br/>
                            dsa=ZFMK_BioCASe_UJDIPPhylcoll_All<br/>
                            inst=Uni Jena<br/>
                            col=UJ-Diptera<br/>
                            cat=UJ-DIP-Phylcoll-10000000<br/>
                        </div>
                    </fieldset>


                </div>
	    </div>
		<hr>
            <div id="service-forms-dep">

                <h3>get Data Sources <i>(deprecated! Please use endpoint /datasets)</i></h3>
                <div>
                    <form action="data-sources/" method="GET" target="webservices">
                        no parameters required
                        <br/>
                        <input name="provider_id" placeholder="provider_id" type="text"/>
                        <br/>
                        <input type="submit" value="go !"/>
                        <div class="direct-call">
                            <a target="webservices" href="#" title=""> > direct link</a>
                        </div>
                    </form>
                    <div class="output"></div>
                </div>

                <h3>get Useful Links <i>(deprecated! Please use endpoint /datasets)</i></h3>
                <div>
                    <form action="useful-links/" method="GET" target="webservices">
                        Please supply values for at least one field.
                        <br/>
                        <input name="provider_id" placeholder="provider_id" type="text"/>
                        <br/>
                        <input name="dataset_id" placeholder="dataset_id" type="text"/>
                        <br/>
                        <input type="submit" value="go !"/>
                        <div class="direct-call">
                            <a target="webservices" href="#" title=""> > direct link</a>
                        </div>
                    </form>
                    <div class="output"></div>
                </div>


                <h3>get XML Archives <i>(deprecated! Please use endpoint /datasets)</i></h3>
                <div>
                    <form action="xml-archives/" method="GET" target="webservices">
                        no parameters required
                        <br/>
                        <input name="provider_id" placeholder="provider_id" type="text"/>
                        <br/>
                        <input name="dataset_id" placeholder="dataset_id" type="text" />
                        <br/>
                        <input type="submit" value="go !"/>
                        <div class="direct-call">
                            <a target="webservices" href="#" title=""> > direct link</a>
                        </div>
                    </form>
                    <div class="output"></div>
                </div>

            </div>

	   <!--
            <h2>Direct Call</h2>
            <ul>
                <li>
                    <p>You may have to supply some of the following GET-Parameters:
                        <br/>
                        <b>provider</b> (Provider ID),
                        <b>name</b> (Provider Name),
                        <b>dsa</b> (Data Source Access Point)
                    </p>
                    <p>
                        <i>Click on a link below will output the resulting JSON in a new tab.</i>
                    </p>
                </li>
                <li><a target="webservices"
                       href="<?php echo $server_url ?>providers/"
                       ><?php echo $server_url ?>providers/</a></li>
                <li><a target="webservices"
                       href="<?php echo $server_url ?>providers/?provider=2"
                       ><?php echo $server_url ?>providers/?provider=1</a></li>
                <li><a target="webservices"
                       href="<?php echo $server_url ?>providers/?name=MfN"
                       ><?php echo $server_url ?>providers/?name=MfN</a></li>
                <li><a target="webservices"
                       href="<?php echo $server_url ?>data-sources/"
                       ><?php echo $server_url ?>data-sources/</a> (gets DSA-Points)</li>
                <li><a target="webservices"
                       href="<?php echo $server_url ?>useful-links/?provider=1&dsa=1"
                       ><?php echo $server_url ?>useful-links/?provider=1&dsa=1</a> (gets Useful Links of DSA)</li>
                <li><a target="webservices"
                       href="<?php echo $server_url ?>useful-links/?provider=1"
                       ><?php echo $server_url ?>useful-links/?provider=1</a> (gets Useful Links)</li>
                <li><a target="webservices"
                       href="<?php echo $server_url ?>xml-archives/?provider=1"
                       ><?php echo $server_url ?>xml-archives/?provider=1</a> (gets Archives)</li>
                <li><a target="webservices"
                       href="<?php echo $server_url ?>capabilities/?provider=1&dsa=mfn_PAL"
                       ><?php echo $server_url ?>capabilities/?provider=1&dsa=mfn_PAL</a> (gets Capabilities)</li>
            </ul>
	-->

        </div>


        <script type="text/javascript">
            $(document).ready(function () {

                $("#menuInfo").html("Webservices");
                $("#imprint a").attr("href", "../info/impressum.php");

                $("#service-forms").accordion({
                    heightStyle: "content"
                });
                $("#service-forms-dep").accordion({
                    heightStyle: "content"
                });

                $("#footer-control a").on("click", function () {
                    $("#footer").toggle("slow");
                });

                var serverUrl = "<?php echo $server_url ?>";

                if (!library)
                    var library = {};

                library.json = {
                    replacer: function (match, pIndent, pKey, pVal, pEnd) {
                        var key = '<span class=json-key>';
                        var val = '<span class=json-value>';
                        var str = '<span class=json-string>';
                        var r = pIndent || '';
                        if (pKey)
                            r = r + key + pKey.replace(/[": ]/g, '') + '</span>: ';
                        if (pVal)
                            r = r + (pVal[0] == '"' ? str : val) + pVal + '</span>';
                        return r + (pEnd || '');
                    },
                    prettyPrint: function (obj) {
                        var jsonLine = /^( *)("[\w]+": )?("[^"]*"|[\w.+-]*)?([,[{])?$/mg;
                        return JSON.stringify(obj, null, 3)
                                .replace(/&/g, '&amp;').replace(/\\"/g, '&quot;')
                                .replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                .replace(jsonLine, library.json.replacer);
                    }
                };


                $("form").on("submit", function (event) {
                    event.preventDefault();
                    contentBox = $(this).parent().find(".output");
                    contentBox.html("JSON result will be displayed here... ");
                    urlBox = $(this).parent().find(".direct-call a");
                    urlBox.attr("href", serverUrl + $(this).attr("action") + "?" + $(this).serialize());
                    urlBox.attr("title", serverUrl + $(this).attr("action") + "?" + $(this).serialize());
                    urlBox.css("visibility", "visible");

                    $.ajax({
                        type: "GET",
                        url: $(this).attr("action"),
                        data: $(this).serialize(),
                        dataType: "json"
                    })
                            .fail(function (jqXHR, textStatus, errorThrown) {
                                console.log("webservice failed: " + textStatus);
                            })
                            .always(function () {
                                //console.log("finished");
                            })
                            .done(function (data) {
                                //console.log(data);
                                //console.log(JSON.stringify(data));
                                contentBox.html("<pre>" + library.json.prettyPrint(data) + "</pre>");
                            });

                });

            });
        </script>
    </body>
</html>
