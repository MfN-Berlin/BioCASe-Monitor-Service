<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/admin/manageUser.php
 * @brief backend: manage user
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
 */
function bin2crud($n) {
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
        <title>BioCASe Monitor - User Management</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

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
        <script src="../js/lib/bootstrap-3.3.7/js/bootstrap.js"></script>
        <script src="../js/lib/jquery-ui-1.11.4/jquery-ui.min.js"></script>
        <script src="../js/general.js"></script>
        <script src="../js/backend.js"></script>
    </head>
    <body>

        <?php
        $title = "Profile Manager";
        include("./navbar.php");
        $infomessage = " ";

        if ($_POST && $_POST["formsent"]) {
            if ($_POST["adduser"]) {
                $currentuser = $_POST["profile"][count($_POST["profile"]) - 1];
                try {
                    $sql = "INSERT INTO user (username, fullname, avatar, email, password)";
                    $sql .= " VALUES (:username, :fullname, :avatar, :email, :password)";
                    $values = array(
                        ":username" => $currentuser["username"],
                        ":fullname" => $currentuser["fullname"],
                        ":avatar" => $currentuser["avatar"],
                        ":email" => $currentuser["email"],
                        ":password" => md5($currentuser["newpassword"] . SALT)
                    );
                    $stmt = $db->prepare($sql);
                    $stmt->execute($values);
                } catch (PDOException $e) {
                    echo $e->getMessage() . "<br>" . $e->getTraceAsString();
                }
                $infomessage .= $currentuser["username"] . " CREATED. <br/>";

                try {
                    $sql = "INSERT INTO auth (username, institution_id, rights)";
                    $sql .= " VALUES (:username, :institution_id, :rights)";
                    $values = array(
                        ":username" => $currentuser["username"],
                        ":institution_id" => $currentuser["provider"],
                        ":rights" => $currentuser["rights"]
                    );
                    $stmt = $db->prepare($sql);
                    $stmt->execute($values);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                    echo $e->getTraceAsString();
                }
                $infomessage .= $currentuser["username"] . " RIGHTS GIVEN. <br/>";
            } else {
                foreach ($_POST["profile"] as $currentuser) {
                    $error = false;
                    if ($currentuser["newpassword"]) {
                        if (strcmp($currentuser["newpassword"], $currentuser["newpassword2"])) {
                            $error = true;
                            $infomessage .= $currentuser["username"]
                                    . ": Passwords differ. Please retype your password.<br/>";
                        } else {
                            try {
                                $sql = "UPDATE user "
                                        . " SET fullname=:fullname, avatar=:avatar, email=:email, password=:password"
                                        . " WHERE username=:username";
                                $values = array(
                                    ":username" => $currentuser["username"],
                                    ":fullname" => $currentuser["fullname"],
                                    ":avatar" => $currentuser["avatar"],
                                    ":email" => $currentuser["email"],
                                    ":password" => md5($currentuser["newpassword"] . SALT)
                                );
                                $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                                $stmt->execute($values);
                            } catch (PDOException $e) {
                                echo $e->getMessage();
                                echo $e->getTraceAsString();
                            }
                            $infomessage .= $currentuser["username"]
                                    . ":" . $currentuser["provider"]
                                    . " password changed <br/>";
                        }
                    } elseif ($currentuser["newpassword2"]) {
                        $error = true;
                        $infomessage .= $currentuser["username"]
                                . ": Passwords differ. Please retype your password.<br/>";
                    } else {
                        // password is not altered
                        try {
                            $sql = "UPDATE user SET fullname=:fullname, avatar=:avatar, email=:email "
                                    . "WHERE username=:username";
                            $values = array(
                                ":username" => $currentuser["username"],
                                ":fullname" => $currentuser["fullname"],
                                ":avatar" => $currentuser["avatar"],
                                ":email" => $currentuser["email"]
                            );
                            $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                            $stmt->execute($values);
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                            echo $e->getTraceAsString();
                        }
                        $infomessage .= $currentuser["username"]
                                . ":" . $currentuser["provider"] . " <br/>";
                    }
                }
            }
            if (!$error) {
                $infomessage.=" <br/>updated.";
            }
            ?>

            <script>
                var hasError = <?php echo ($error ? 1 : 0) ?>;
                //console.log(infomsg);
                //console.log(hasError);
                if (hasError == 1) {
                    $("#system-message").addClass("modal");
                     $("#system-message").html("<?php echo $infomessage ?>");
                    //$("#system-message").show();
                    $("#system-message").fadeIn(800).delay(3200).fadeOut(800);

                } else {
                    displaySystemMessage("<?php echo $infomessage ?>" , "alert-success");
                }
            </script>
            <?php
        }



// GET USER PROFILE(S)
        try {
            $values = array();
            $sql = "SELECT user.*, auth.* FROM user,auth  WHERE user.username = auth.username ";
            if ($_SESSION["rights"] < 31) {
                $sql .= " AND user.username = :username";
                $values = array(
                    ":username" => $_SESSION["username"]
                );
            }
            $sql .= " ORDER BY auth.institution_id, user.username";

            $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $stmt->execute($values);

            $userdata = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $userdata[] = $row;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }


        if ($_SESSION["rights"] == 31) {
            $sql = "SELECT * FROM institution ";
            $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $stmt->execute();
            $providerdata = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $providerdata[] = $row;
            }
            include("manageSuperuserForm.php");
        } else {
            include("manageUserForm.php");
        }
        ?>


    </body>
</html>
