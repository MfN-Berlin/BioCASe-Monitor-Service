<?php

/**
 * BioCASe Monitor 2.1
 * @copyright  (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/lib/auth.php
 * @brief authentication
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

/**
 * generate password form
 *
 * @param string $p_destForm URL called on submit
 * @return html form
 */
function generate_password_form($p_destForm) {
    $returnedText = "";
    $returnedText.="<form  name=\"login\" action=\"" . $p_destForm . "\" method=\"POST\">";
    $returnedText.="<input type=\"text\" name=\"username\" placeholder=\"admin username\" value=\"\"/>";
    $returnedText.="<input type=\"password\" name=\"auth_field\" placeholder=\"admin password\"/>";
    $returnedText.="<input type=\"submit\" value=\"Connect\" />";
    $returnedText.="</form>";
    return $returnedText;
}

/**
 * generate logout form
 *
 * @param string $p_destForm URL called on submit
 * @return html form
 */
function generate_logout_form($p_destForm) {
    $returnedText = "<div style=\"display:inline-block; padding-left:5px;\">";
    $returnedText.="<form name=\"logout\" action=\"" . $p_destForm . "\" method=\"POST\">";
    $returnedText.="<input type=\"hidden\" name=\"log_out\" value=\"1\" />";
    $returnedText.="<input type=\"image\" title=\"administration log-out\" src=\"../images/glyphicons/glyphicons-388-log-out.png\"   value=\"Log-out\" />";
    $returnedText.="</form></div>";
    return $returnedText;
}

/**
 * check password matching
 * the stored password in the datbase is a salted and hashed string
 *
 * @param string $username  the username
 * @param string $password  the non encrypted password
 * @return boolean false
 */
function doPasswordComparison2($username, $password) {
    global $db;
    $encrypted_password = md5($password . SALT);
    try {
        $sql = "SELECT user.*, auth.* FROM user,auth "
                . " WHERE user.username = auth.username "
                . " AND user.username='$username' "
                . " AND user.password='$encrypted_password'";
        $stmt = $db->query($sql);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sql = "UPDATE auth"
                    . " SET last_connection='" . floor(microtime(true)) . "'"
                    . " WHERE username='$username' ";
            $stmt = $db->query($sql);
            // returns result of SELECT statement above
            return $row;
        }
        return false;
    } catch (PDOException $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
        return false;
    }
    return false;
}

/**
 * checks if the authorization token is valid
 *
 * @param string $authtoken  the token string defined on first login
 * @return boolean
 */
function isAuthorized($authtoken) {
    global $db;
    try {
        $sql = "SELECT * FROM auth_token WHERE token='$authtoken'";
        $stmt = $db->query($sql);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
        return false;
    }
    return false;
}
