<?php

/**
 * @package    Joomla.cli.update_plugins
 *
 * @copyright  Copyright (C) 2018 After Digital
 * @author     Stu Mileham <stuart.mileham@afterdigital.co.uk>
 * 
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version. 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class MultiSitesUpdateHelper
{
  /**
   * Get all of the multisites
   * 
   * @return array
   */
  public function getSites()
  {
    $sites = [];
    $directories = array_merge(glob(__DIR__ . "/../../components/com_mightysites/configuration/configuration*.php"), glob(__DIR__ . "/../configuration*.php"));

    foreach ($directories as $file) {
      $config = file_get_contents($file);
      $rows = explode("\n", $config);
      $config = $this->parseDbDetails($rows);
      if ($config) {
        $sites[] = $config;
      }
    } 
    return $sites;
  }

  /**
   * Parse db details from multiple configuration files
   * We can't just include all of them because they are all JConfig classes
   * So we are parsing them as text files and extracting what we need in an array
   */
  protected function parseDbDetails($rows) {
    $db = 'public $db =';
    $dbprefix = 'public $dbprefix =';
    $host = 'public $host =';
    $password = 'public $password =';
    $user = 'public $user =';

    $site = [];
    foreach ($rows as $row) {
      if (strpos($row, $db) !== false) {
        $site['db'] = str_replace('\';', '', substr($row, strpos($row, '\'') + 1));
      }
      if (strpos($row, $dbprefix) !== false) {
        $site['dbprefix'] = str_replace('\';', '', substr($row, strpos($row, '\'') + 1));
      }
      if (strpos($row, $host) !== false) {
        $site['host'] = str_replace('\';', '', substr($row, strpos($row, '\'') + 1));
      }
      if (strpos($row, $password) !== false) {
        $site['password'] = str_replace('\';', '', substr($row, strpos($row, '\'') + 1));
      }
      if (strpos($row, $user) !== false) {
        $site['user'] = str_replace('\';', '', substr($row, strpos($row, '\'') + 1));
      }
    }

    return (count($site)) ? $site : false;
  }
}