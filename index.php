<?php
/**
 * BioCASe Monitor 2.1
 *
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/index.php
 * @brief entry point, using class definition of Bms
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

require_once("bms.class.php");


/**
 * GET parameter for static routing, passed to the frontController
 */
$route = filter_input(INPUT_GET, 'action');


$myBms = new Bms();

$myBms->debugmode = (isset($_GET["debug"]) ? $_GET["debug"] : DEBUGMODE);
$myBms->custom_layout = (isset($_GET["custom"]) ? $_GET["custom"] : CUSTOM);
$myBms->getMessages();

$myBms->frontController($route);

//////////////////////////////////////
?><!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
            <title>BioCASe Monitor</title>

            <meta charset="utf-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1"/>

            <link rel="stylesheet" type='text/css' href="js/lib/bootstrap-3.3.7/css/bootstrap.min.css"/>

            <link rel="stylesheet" type="text/css" href="css/general.css"/>
            <link rel="stylesheet" type="text/css" href="css/frontend.css"/>

            <script src="js/lib/jquery-2.1.4.min.js"></script>
            <script src="js/lib/bootstrap-3.3.7/js/bootstrap.js"></script>

            <script src="js/general.js"></script>
            <script src="js/frontend.js"></script>

            <?php
            if ($myBms->debugmode == "1") {
                echo '<script src="js/dev.js"></script>';
                echo '<link rel="stylesheet" type="text/css" href="css/debug.css"/>';
            }
            if ($myBms->custom_layout == "1") {
                echo '<script src="js/custom.js"></script>';
                echo '<link rel="stylesheet" type="text/css" href="css/custom.css"/>';
            }
            ?>

    </head>
    <body>

        <?php
        if ($_REQUEST && !empty($_REQUEST['log_out'])) {
            if ($_REQUEST['log_out'] == 1) {
                $formContent = $loginForm;

                $_SESSION['authenticated'] = 0;
                $_SESSION = array();
                session_destroy();
            }
        }
        ?>
        <nav class="navbar  navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <figure>
                        <a href="./"><img src="./images/biocase-logo.jpg" alt="logo" title="BioCASe Monitor Start Page"/></a>
                        <figcaption>Monitor</figcaption>
                    </figure>
                </div>
                <div class="navbar-header">
                    <?php
                    if ($myBms->custom_layout == "1") {
                        include "./config/custom/customize.php";
                        echo "<figure><a href='$custom_url' target='_blank'><img src='$custom_logo' alt='$custom_institution_shortname' style='height:55px;padding-left:20px;padding-right:20px;'/></a>
</figure>";
                    }
                    ?>
                </div>

                <ul class="nav navbar-nav navbar-left">
                    <!-- <li class="active"><a href="#">Home</a></li> -->
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

                    <li><a href="#" class="warning glyphicon glyphicon-flash"> Notice</a></li>

                    <li>
                        <a href="#" id="verbose-control" class="glyphicon glyphicon-eye-open" title="show/hide progress bars"
                           > on</a>
                    </li>

                    <li>
                        <a title="overall response time from the BioCASe Provider Software installations"><span id="global-time-elapsed" >0</span></a>
                    </li>

                </ul>

                <ul class="nav navbar-nav navbar-right">


                    <?php if (!$_SESSION["authenticated"]) { ?>
                        <li>
                            <a href="admin/index.php" title="Administration" class="glyphicon glyphicon-log-in"> Login</a>
                        </li>

                        <?php
                    } else {
                        echo "<li><a href='admin/manageUser.php' title='profile' class='glyphicon glyphicon-user'> " . $_SESSION["fullname"] . "</a></li>";
                        echo "<li><a href='index.php?log_out=1' title='log out' class='glyphicon glyphicon-log-out'> Logout</a></li>";
                    }
                    ?>

                    <li><a href="./services/" title="API" class="glyphicon glyphicon-globe"> Webservices</a></li>


                    <li><a id="footer-control" href="#"
                           title="Legal Infos"
                           class="glyphicon glyphicon-info-sign"> Legal</a></li>
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
                        <a href="https://www.museumfuernaturkunde.berlin"
                           title="https://www.museumfuernaturkunde.berlin"
                           target="_blank">
                            <img src="./images/mfn_logo_klein.png" 
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

        <div id="system-message"></div>

        <div id="main"></div>

    </body>
</html>
