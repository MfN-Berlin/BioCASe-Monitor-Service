<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/navbar.php
 * @brief backend: display navigation bar
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

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <figure>
                <a href="../"><img src="../images/biocase-logo.jpg" alt="logo" title="BioCASe Monitor Start Page"/></a>
                <figcaption>Monitor</figcaption>
            </figure>
        </div>
        <div class="navbar-header">
            <?php
            if (CUSTOM == "1") {
                include "../config/custom/customize.php";
                echo "<figure><a href='$custom_url' target='_blank'><img src='../$custom_logo' alt='$custom_institution_shortname' style='height:55px;padding-left:20px;padding-right:20px;'/></a>
</figure>";
            }
            ?>
        </div>

        <div class="navbar-header">
            <h3 style='margin-top:12px;'><?php echo $title; ?></h3>
        </div>

        <ul class="nav navbar-nav navbar-left">

            <li>
                <a href="manageProvider.php"
                   id="menuProvider"
                   title="manage provider metadata"
                   class="glyphicon glyphicon-cog"> Dashboard</a>
            </li>

        </ul>

        <ul class="nav navbar-nav navbar-right">

            <?php if (!$_SESSION["authenticated"]) { ?>
                <li>
                    <a href="index.php" title="Administration" class="glyphicon glyphicon-log-in"> Login</a>
                </li>

                <?php
            } else {
                echo "<li><a href='manageUser.php' title='profile' class='glyphicon glyphicon-user'> " . $_SESSION["fullname"] . "</a></li>";
                echo "<li><a href='index.php?log_out=1' title='log out' class='glyphicon glyphicon-log-out'> Logout</a></li>";
            }
            ?>

            <li><a href="../services/" title="API" class="glyphicon glyphicon-globe"> Webservices</a></li>

            <li>
                <a id="footer-control" title="Legal Infos" class="glyphicon glyphicon-info-sign" xxxdata-toggle="modal" data-target="#footer"> Legal</a>
                <div id="footer" style="background:white; border: 2px solid black;">
                    <div>
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
            </li>

        </ul>
    </div>
</nav>

<div id="adminStatus"></div>

<div id="global-link-categories"></div>
<div id="all-filters"></div>

<div id="system-message"></div>
