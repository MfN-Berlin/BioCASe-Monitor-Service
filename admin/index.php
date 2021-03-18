<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/index.php
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

$custom_layout = (isset($_GET["custom"]) ? $_GET["custom"] : 1);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Administer BioCASe providers</title>

    <link rel="stylesheet" type='text/css' href="../js/lib/bootstrap-3.3.7/css/bootstrap.min.css" />
    <link rel="stylesheet" type='text/css' href="../js/lib/jquery-ui-1.11.4/jquery-ui.min.css" />

    <link rel="stylesheet" type="text/css" href="../css/general.css" />
    <link rel="stylesheet" type="text/css" href="../css/backend.css" />

    <script src="../js/lib/jquery-2.1.4.min.js"></script>
    <script src="../js/lib/bootstrap-3.3.7/js/bootstrap.js"></script>
    <script src="../js/lib/jquery-ui-1.11.4/jquery-ui.min.js"></script>

    <script src="../js/general.js"></script>
    <script src="../js/backend.js"></script>
    <script>
        debugmode = "<?php echo $debugmode; ?>";
        userName = "<?php echo $_SESSION['username']; ?>";
        userProvider = "<?php echo $_SESSION['provider']; ?>";
        userRights = parseInt("<?php echo $_SESSION['rights']; ?>");
        linkCategories = "";
        console.log("user name: " + userName);
        console.log("user provider: " + userProvider);
        console.log("user rights: " + userRights + (userRights & 8 == 8 ? " is advanced" : " is standard"));

        //$("#pr_name").html("<?php echo addslashes($htmlOptionList); ?>");
    </script>

    <?php
    echo ($_SESSION['authenticated'] == 1 ? '<meta http-equiv="refresh" content="0;URL=\'/admin/manageProvider.php\'" />' : '');
    ?>
</head>

<body>
    <?php

    $formContent = "";
    $loginForm = '
            <div class="container">
                <div class="row">
                    <div class="col-md-12 alert alert-info">
                        Please provide your credentials.
                    </div>
               </div>
                <div class="row">
                    <div class="col-md-12">
                        <form method="POST">
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" class="form-control" id="username" name="username">
                            </div>
                            <div class="form-group">
                                <label for="pwd">Password:</label>
                                <input type="password" class="form-control" id="pwd" name="auth_field">
                            </div>
                            <button type="submit" class="btn btn-default">Submit</button>
                        </form>
                    </div>
                </div>
            </div>';

    if (!($_SESSION || $_REQUEST || $_SESSION['authenticated'])) {
        $formContent = $loginForm;
    } else

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

            $formContent = '<script> window.location.href = "/admin/manageProvider.php"; </script>';
        } else {

            $_SESSION['authenticated'] = 0;
            $formContent = $loginForm;
        }
    } else {
        if ($_REQUEST && !empty($_REQUEST['log_out'])) {
            if ($_REQUEST['log_out'] == 1) {
                $formContent = $loginForm;

                $_SESSION['authenticated'] = 0;
                $_SESSION = array();
                session_destroy();
            }
        } elseif ($_SESSION['authenticated'] == 1) {
            // user is authenticated, do nothing

        } else {
            // begin of cycle (no session variable yet)
            $formContent = $loginForm;
        }
    }

    include("./navbar.php");
    echo $formContent;
    ?>

</body>

</html>