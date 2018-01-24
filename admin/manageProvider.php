<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/manageProvider.php
 * @brief backend: backend: manage Provider
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
if (!$_SESSION["authenticated"]) {
    header('Location: index.php');
    exit;
}

include("../lib/auth.php");

/**
 * display a number as "CRUD"
 *
 * @param string $n
 * @return string
 */function bin2crud($n) {
    $p2 = 1;
    $out = "";
    $crud = str_split("crud"); // create, read, update, delete
    for ($i = 0; $i < 4; $i++) {
        if ($n & $p2) {
            $out.=$crud[$i];
        } else {
            $out.="-";
        }
        $p2 = $p2 << 1;
    }
    return $out;
}

$debugmode = (isset($_GET["debug"]) ? $_GET["debug"] : DEBUGMODE);
?><!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Modify the declaration of a BioCASe provider</title>

        <link rel="stylesheet" type='text/css' href="../js/lib/bootstrap-3.3.7/css/bootstrap.min.css"/>
        <link rel="stylesheet" type='text/css' href="../js/lib/jquery-ui-1.11.4/jquery-ui.min.css"/>

        <link rel="stylesheet" type="text/css" href="../css/general.css"/>
        <link rel="stylesheet" type="text/css" href="../css/backend.css"/>
        
        <?php
        if ($debugmode == "1") {
            echo '<link rel="stylesheet" type="text/css" href="../css/debug.css"/>';
            echo '<script src="../js/dev.js"></script>';
        }
        ?>

        <script src="../js/lib/jquery-2.1.4.min.js"></script>
        <script src="../js/lib/bootstrap-3.3.7/js/bootstrap.min.js"></script>
        <script src="../js/lib/jquery.bootcomplete.js"></script>
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
        </script>
    <body>

        <?php
        $title = "Metadata Catalogue Registration Manager";
        include("./navbar.php");
        include("manageProviderForm.php");
        ?>

    </body>
</html>
