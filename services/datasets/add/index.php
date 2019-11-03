<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @namespace Webservices
 * @file biocasemonitor/services/providers/add/index.php
 * @brief Webservices add provider (needeed for automated population)
 *
 * example call
 * {id:"1",name:"Test-Provider",shortname:"TTT",url:"www.provider.org",datacenter:"Data Center TTT"}
 *
 * http://bms.biocase.org/services/login/add/?authtoken=675577aa70cda36c556cd87169f962d5&json={id:%20%221%22,name:%22Test-Provider%22,shortname:%22TTT%22,url:%22www.provider.org%22,datacenter:%22Data%20Center%20TTT%22}
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

/*
namespace Webservices;

require_once("../../../config/config.php");
require("../../../lib/auth.php");
header('Content-type: application/json, charset=utf-8');
//header('Content-type: text/plain, charset=utf-8');

$isAuthorized = isAuthorized($_GET["authtoken"]);
$json = json_decode($_GET["json"], true);

//$name = filter_input(INPUT_GET, 'name');
//$shortname = filter_input(INPUT_GET, 'shortname');
//$url = filter_input(INPUT_GET, 'url');

echo $json["id"];
echo $json["name"];
echo $json["shortname"];
echo $json["url"];



if (!$isAuthorized || empty($name) || empty($shortname) || empty($url)) {
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
    }
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (\PDOException $e) {
    $output = array();
    $output["error"] = $e->getMessage();
    echo json_encode($output);
}
*/
