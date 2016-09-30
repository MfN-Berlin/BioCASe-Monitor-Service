<?php
/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/core/manageProvider.php
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
    header('Location: admin.php');
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
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="../js/lib/jquery-2.1.4.min.js"></script>
        <script src="../js/lib/jquery-ui-1.11.4/jquery-ui.min.js"></script>
        <script src="../js/general.js"></script>
        <script src="../js/backend.js"></script>
        <link rel="stylesheet" type='text/css' href="../js/lib/jquery-ui-1.11.4/jquery-ui.min.css"/>
        <link rel="stylesheet" type="text/css" href="../css/frame.css"/>
        <link rel="stylesheet" type="text/css" href="../css/backend.css"/>

        <?php
        if ($debugmode == "1") {
            echo '<link rel="stylesheet" type="text/css" href="../css/debug.css"/>';
            echo '<script src="../js/dev.js"></script>';
        }
        ?>

    </head>
    <body>

        <?php
        $page_title = "manage provider";
        include("./topbar.php");

        try {
            if ($_SESSION["rights"] == 31) {
                $sql = "SELECT institution.id, institution.shortname, institution.name, '31' as rights "
                        . "FROM institution "
                        . "ORDER BY institution.shortname";
                $values = array();
            } else {
                $sql = "SELECT institution.id, institution.shortname, institution.name,  auth.rights
                    FROM auth
                    JOIN institution
                    ON auth.institution_id = institution.id
                    WHERE auth.username = :username
                    ORDER BY institution.shortname";
                $values = array(
                    ":username" => $_SESSION["username"]
                );
            }
            $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $stmt->execute($values);

            $htmlOptionList = "<option value='-1'>please select a provider</option>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $htmlOptionList .= "<option value='" . $row["id"] . "'>"
                        . $row["id"] . " [" . $row["shortname"] . "]  " . $row["name"]
                        . " [" . bin2crud($row["rights"]) . "]"
                        . "</option>";
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }
        include("manageProviderForm.php");
        ?>

        <script>
            $(document).ready(function () {
                $("#menuInfo").html('Registration Manager of a BioCASe provider in the Metadata Catalogue');
                $("#pr_name").html("<?php echo addslashes($htmlOptionList); ?>");
            });
        </script>

    </body>
</html>
