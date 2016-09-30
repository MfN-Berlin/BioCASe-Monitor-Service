<?php
/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/topbar.php
 * @brief display top bar
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
?>

<div id="topBar" >

    <div id="home">
        <figure>
            <a href="../"><img src="../images/biocase-logo.jpg" height="60" alt="logo"
                               title="BioCASe Monitor Start Page"/></a>
            <figcaption>Monitor</figcaption>
        </figure>
        <figure>
            <a href="http://www.gfbio.org" target="_blank"
               ><img src="../images/800px-GFBio_logo_claim_png.png" height="75" alt="GFBIO"/></a>
            <figcaption></figcaption>
        </figure>
    </div>

    <div id="menuLinks">
        <div id="adminStatus">

            <?php if (!$_SESSION["authenticated"]) { ?>
                guest
                <a href="index.php"
                   title="Administration"
                   ><img alt="administration"
                      src="../images/glyphicons/glyphicons-387-log-in.png" height="20"/></a>
                    <?php
                } else {
                    echo $_SESSION["fullname"];
                    echo "<a href='manageUser.php' title='my profile'><img alt='user avatar' src='../images/glyphicons/glyphicons-4-user.png' height='20'/></a>";
                    echo generate_logout_form("./admin.php");
                }
                ?>

            <a href="../services/"
               title="Webservices"
               ><img alt="webservices"
                  src="../images/RESTful.png"/></a>

            <a id="footer-control"
               title="Legal Infos"
               ><img alt="info"
                  src="../images/glyphicons/glyphicons-196-circle-info.png"/></a>
        </div>

        <div id="footer">
            <ul class="impressum">

                <li class="menuItem">
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
                            <img src="../images/mfnlogo_167_190.jpg" height="30"
                                 alt="Museum f&uuml;r Naturkunde, Berlin"/></a>
                    </figure>
                </li>

                <li class="menuItem">
                    <a href="http://biocasemonitor.biodiv.naturkundemuseum-berlin.de/index.php/Documentation" target="_blank"
                       >Documentation</a>
                </li>

                <li class="menuItem" id="imprint">
                    <a href="../info/impressum.php" target="_blank"
                       >Imprint</a>
                </li>
            </ul>
        </div>
    </div>


    <div class="mainMenu">
        <div id="menuInfo"></div>
        <ul>
            <li class="menuItem admin" id="loginForm">
            </li>

            <li class="menuItem admin">
                <a href="manageProvider.php" id="menuProvider"
                   title="manage provider metadata">Provider</a>
            </li>

            <li class="menuItem admin">
                <a href="manageUser.php" id="menuUser"
                   title="manage user profile">User</a>
            </li>

        </ul>
    </div>

    <div id="adminStatus"></div>


    <div id="global-link-categories"></div>
    <div id="all-filters"></div>

    <div id="system-message"></div>

</div>

