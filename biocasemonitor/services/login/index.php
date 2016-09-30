<?php

/**
 * BioCASe Monitor 2.0
 * Copyright (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @namespace Webservices
 * @file biocasemonitor/services/login/index.php
 * @brief Webservices login (needeed for automated population)
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

namespace Webservices;

require_once("../../config/config.php");

$username = filter_input(INPUT_GET, 'username');
$password = filter_input(INPUT_GET, 'password');

/**
 * handle user login
 *
 * @param type $username
 * @param type $password
 */
function manageLogin($username, $password) {
    global $db;

    if (empty($username) || empty($password)) {
        $output = array();
        $output["error"] = "Please provide credentials";
        echo json_encode($output);
        exit;
    }

    try {
        $sql = "SELECT user.* FROM user WHERE username LIKE :username AND password LIKE :password";
        $values = array();
        $values[":username"] = $username;
        $values[":password"] = md5($password . SALT);

        $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $stmt->execute($values);

        $result = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result["authtoken"] = md5(microtime() . SALT);

            $sql = "INSERT INTO auth_token (token) VALUES(:authtoken)";
            $values = array();
            $values[":authtoken"] = $result["authtoken"];
            $stmt = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $stmt->execute($values);
        }
        echo json_encode($result, JSON_PRETTY_PRINT);
    } catch (\PDOException $e) {
        $output = array();
        $output["error"] = $e->getMessage();
        echo json_encode($output);
    }
}

header('Content-type: application/json, charset=utf-8');
echo manageLogin($username, $password);

