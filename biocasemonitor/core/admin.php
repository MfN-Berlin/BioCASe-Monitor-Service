<?php
/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/admin.php
 * @brief backend entry point
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
require_once("../lib/auth.php");
?><!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Administer BioCASe providers</title>
        <script src="../js/lib/jquery-2.1.4.min.js"></script>
        <script src="../js/lib/jquery-ui-1.11.4/jquery-ui.min.js"></script>
        <script src="../js/general.js"></script>
        <link rel="stylesheet" type='text/css' href="../js/lib/jquery-ui-1.11.4/jquery-ui.min.css"/>
        <link rel="stylesheet" type="text/css" href="../css/frame.css"/>
        <link rel="stylesheet" type="text/css" href="../css/backend.css"/>
    </head>
    <body>
        <?php
        if ($_REQUEST && !empty($_REQUEST['auth_field'])) {
            $user = doPasswordComparison2($_REQUEST['username'], $_REQUEST['auth_field']);

            if ($user) {
                // user exists, password is correct
                $_SESSION['authenticated'] = 1;
                $_SESSION['rights'] = $user["rights"];
                $_SESSION['provider'] = $user["institution_id"];

                $_SESSION["username"] = $user["username"];
                $_SESSION["fullname"] = $user["fullname"];
                $_SESSION["email"] = $user["email"];
                $formContent = "Welcome <b>" . $_SESSION["fullname"] . "</b> !"
                        . " Please choose an action. ";
            } else {
                $_SESSION['authenticated'] = 0;
                $formContent = "Wrong credentials!" . generate_password_form("admin.php");
            }
        } else {
            if ($_REQUEST && !empty($_REQUEST['log_out'])) {
                if ($_REQUEST['log_out'] == 1) {
                    $formContent = "Good bye " . $_SESSION["fullname"] . " !" . generate_password_form("admin.php");
                    $_SESSION['authenticated'] = 0;
                    $_SESSION = array();
                    session_destroy();
                }
            } elseif ($_SESSION['authenticated'] == 1) {
                // user is authenticated, do nothing
                $formContent = '<span class="header">Go ahead, ' . $_SESSION["fullname"] . ' !</span>'
                        . generate_logout_form("admin.php");
            } else {
                // begin of cycle (no session variable yet)
                $formContent = "Please provide your credentials"
                        . generate_password_form("admin.php");
            }
        }
        include("./topbar.php");
        ?>
        <script type="text/javascript">
            $(document).ready(function () {

                // set Title
                $("#menuInfo").html('Administration');

                // populate and show login form
                $("#loginForm").html("<?php echo addslashes($formContent); ?>");
                $("#loginForm").show();

                // show/hide admin menuItems
                var adminStatus = <?php echo ($_SESSION["authenticated"] ? 1 : 0); ?>;
                if (adminStatus) {
                    $(".mainMenu li.admin").show();
                }

            });
        </script>
    </body>
</html>
