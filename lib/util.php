<?php

/**
 * BioCASe Monitor 2.1
 * @copyright  (C) 2015 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/lib/util.php
 * @brief utilities
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

/**
 * get basic infos of given provider
 *
 * @param int $idProvider
 * @return Array
 */
function getProviderBasicInfos($idProvider) {
    global $db;
    try {
        $sql = "SELECT
            institution.id as providerId,
            institution.name, institution.shortname, institution.url as providerUrl,
            institution.pywrapper
           FROM
            institution
          WHERE
            institution.id = '$idProvider'
            ";

        $stmt = $db->query($sql);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row;
        }
        return json_encode($provider, JSON_PRETTY_PRINT);
    } catch (\PDOException $e) {
        return $e->getMessage();
    }
}

/**
 * sluggify
 *
 * @param string $str string to be sluggified
 * @return string $sluggified string
 */
function sluggify($str) {
    $clean = $str;
    $clean = preg_replace("/[^a-zA-Z0-9\/_| -\.]/", '', $clean);
    $clean = preg_replace("/[\/_| -\.]+/", '-', $clean);
    return strtolower(trim($clean, '-'));
}

