<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * impressum
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
?><!doctype html>
<html lang="en">
    <head>
        <title>BioCASe Monitor - Impressum</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link rel="stylesheet" type="text/css" href="../css/general.css"/>
        <link rel="stylesheet" type="text/css" href="../css/frontend.css"/>
        <style>
            ul {clear:both; width:100%; margin:0; margin-left:24px; padding:0;}
            li {float:left; padding-right:20px;}
            h3 {width:100%; padding:10px; clear:both; background:#efefef;}
            dd {padding:10px;}
            fieldset {margin-bottom:20px;}
            legend {font-weight:bold; padding-left:1em; padding-right:1em;}
            #menuLinks {display:none}
        </style>
    </head>
    <body>
        <?php
        require_once("../config/config.php");
        include("../admin/topbar.php");
        ?>

        <fieldset class="content">
            <legend>Imprint and Disclaimer</legend>
            <ul>
                <li><a href="http://www.open-up.eu" title="www.open-up.eu" target="_blank"
                       ><img src="../images/OpenUp-Logo150x150.png" alt="OpenUp!"/></a></li>
                <li><a href="http://www.gbif.de" title="www.gbif.de" target="_blank"
                       ><img src="../images/GBIF-D-Logo150x150.png" alt="GBIF-D"/></a></li>
                <li><a href="http://www.biocase.org" title="www.biocase.org" target="_blank"
                       ><img  src="../images/BioCASE-Logo150x105.jpg" alt="BioCASE"/></a></li>
                <li><a href="http://www.gfbio.org" title="http://www.gfbio.org" target="_blank"
                       ><img src="../images/800px-GFBio_logo_claim_png.png"  alt="GFBIO" width="200"/></a></li>
            </ul>

            <h3>Funding bodies Version 1.x</h3>
            <ul>
                <li><a href="http://www.europeana.eu"
                       title="http://www.europeana.eu"
                       target="_blank"
                       ><img width="112" height="182"
                          src="../images/Europeana_logo_ProjectGroupMember.gif" alt="Europeana"/></a></li>
                <li><a href="http://ec.europa.eu/information_society/activities/ict_psp/about/index_en.htm"
                       title="http://ec.europa.eu/information_society/activities/ict_psp/about/index_en.htm"
                       target="_blank"
                       ><img width="300" height="183"
                          src="../images/ict_psp_logo.jpg" alt="ICT-PSP"/></a></li>
                <li><a href="http://ec.europa.eu/information_society/activities/econtentplus/closedcalls/econtentplus/programme/index_en.htm"
                       title="http://ec.europa.eu/information_society/activities/econtentplus/closedcalls/econtentplus/programme/index_en.htm"
                       target="_blank"
                       ><img width="465" height="110"
                          src="../images/logo-ecplus.jpg" alt="eContent+"/></a></li>
                <li><a href="http://www.bmbf.de/"
                       title="http://www.bmbf.de/"
                       target="_blank"
                       ><img width="220" height="110"
                          src="../images/500pxBMBF_Logo.svg.png" alt="BMBF"/></a></li>
            </ul>

            <h3>Funding bodies Version 2.x</h3>
            <ul>
                <li><a href="http://www.dfg.de/" title="http://www.dfg.de/" target="_blank"
                       ><img alt="DFG"
                          src="http://dfg.de/includes/images/dfg_logo.gif" /></a></li>
            </ul>

            <h3>Partner institutions:</h3>
            <ul>
                <li><a href="http://www.africamuseum.be"
                       title="http://www.africamuseum.be" target="_blank"
                       ><img width="130" height="290"
                          src="../images/Africa_ex_clr_EN160_358.jpg"
                          alt="Royal Museum for Central Africa"/></a></li>
                <li><a href="http://www.naturkundemuseum-berlin.de/"
                       title="http://www.naturkundemuseum-berlin.de/" target="_blank"
                       ><img
                            src="../images/mfnlogo_167_190.jpg"
                            alt="Museum für Naturkunde, Berlin"/></a></li>
            </ul>

            <h3>Disclaimer</h3>
            <p>
                There is no warranty for the availability and correctness of this web-tool and it's contents. <br/>
                You use this tool on your one risk! The persons who are involved in the development and/or <br/>
                coordination of this software can not be held responsible for the liability of any damage or <br/>
                disadvantages that might be caused by this tool and service.
            </p>
        </fieldset>


        <fieldset class="content">
            <legend>Credits</legend>
            <dl>
                <dt>Developers:</dt>
                <dd>v2.x
                    <a href="http://www.naturkundemuseum-berlin.de/en/institution/mitarbeiter/pfuhl-thomas"
                       target="_blank"
                       >Thomas Pfuhl</a> (Museum f&uuml;r Naturkunde, Berlin)
                </dd>
                <dd>v1.x
                    <a href="http://www.naturkundemuseum-berlin.de/en/institution/mitarbeiter/gloeckler-falko"
                       target="_blank"
                       >Falko Gl&ouml;ckler</a> (Museum f&uuml;r Naturkunde, Berlin)
                </dd>
                <dd>v1.x
                    <a href="mailto:franck.theeten@africamuseum.be"
                       target="_blank"
                       >Franck Theeten</a> (Royal Museum for Central Africa, Belgium)
                </dd>

                <dt>Scientific coordination:</dt>
                <dd><a href="http://www.naturkundemuseum-berlin.de/en/institution/mitarbeiter/hoffmann-jana/"
                       target="_blank"
                       >Dr. Jana Hoffmann</a> (Museum f&uuml;r Naturkunde, Berlin)
                </dd>
            </dl>
        </fieldset>

        <fieldset class="content">
            <legend>License</legend>
            <p>
                <img src="../images/gplv3-127x51.png" alt="GPL v3"/>
                The software BioCASe Monitor Service is available under GNU General Public License
                (<a href="http://www.gnu.org/licenses/gpl-3.0" target="_blank"
                    >http://www.gnu.org/licenses/gpl-3.0</a>).
            </p>
        </fieldset>

    </body>
</html>
