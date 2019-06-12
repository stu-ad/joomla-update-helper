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

/**
 * A command line utility to attempt to run plugin updates across multisites
 * 
 * Unzip each plugin to be updated into it's own folder within MultiSiteUpdates/Extensions
 * 
 * @example from cli directory: php update_plugins.php
 * 
 * Please do not run this on a live site without thorough testing in a development environment 
 * 
 * If you don't understand what this utility is doing, then I recommend that you do not use it
 * You have been warned, I cannot accept any responsibility for your broken joomla site
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
  require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
  define('JPATH_BASE', dirname(__DIR__));
  require_once JPATH_BASE . '/includes/defines.php';
}

define('JPATH_COMPONENT', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');
define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/com_joomlaupdate');

//define('JPATH_THEMES', JPATH_ADMINISTRATOR . '/templates');

require_once JPATH_BASE . '/includes/framework.php';


// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

require_once JPATH_BASE . '/cli/MultiSiteUpdates/Helper.php';

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Library language
$lang = JFactory::getLanguage();

// Try the files_joomla file in the current language (without allowing the loading of the file in the default language)
$lang->load('files_joomla.sys', JPATH_SITE, null, false, false)
// Fallback to the files_joomla file in the default language
|| $lang->load('files_joomla.sys', JPATH_SITE, null, true);

/**
 * A command line task to attempt to run through plugin updates and install on multisites
 *
 * @since  3.0
 */
class UpdatePluginsCli extends JApplicationCli
{
  private $multisite_mode = false;

  private $terms = "The Author cannot be held liable for any damage to your website
    that arises from using this software.  
    This is free software, distributed under the terms of the GNU General
    Public License version 3 or, at your option, any later version.
    This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
    license. See http://www.gnu.org/licenses/gpl-3.0.html for details.";

  public function doExecute()
  {
    $this->out($this->terms);
    $this->out('--------');

    $this->helper = new \MultiSitesUpdateHelper;
    // Import the dependencies
    $component = JComponentHelper::getComponent('com_installer');

    if ($this->multisite_mode) {

      // Loop around all the mightysites that can be extracted from configuration*.php files
      foreach ($this->helper->getSites() as $site) {

        try {

          // Normalise configuration, then override the database configuration
          $site['driver']   = 'mysqli'; 
          $site['database'] = $site['db'];
          $site['prefix'] = $site['dbprefix'];

          $db = JDatabaseDriver::getInstance($site);

          $app = JFactory::getApplication('administrator');
          
          // Luckily this is not protected, we can set our alternative database directly on JFactory
          // Any subsequent calls to JFactory::getDbo() will return our version
          JFactory::$database = $db;

          $this->updateSite($site);


        // Try and catch any errors with a single site to prevent them crashing the whole thing
        } catch (\Exception $e) {

          $this->out('--could not update '.$site['db'] .'. '. $e->getMessage() . '. line ' . $e->getLine() . '. File ' . $e->getFile());
          continue;
        }

        // Hopefully, if we hit this point, the site has been updated successfully
        $this->out('--updates completed for '. $site['db']);
      }

    } else {
      $config = new JConfig();

      $site = [
        'db' => $config->db,
        'dbprefix' => $config->dbprefix
      ];

      $this->updateSite($site);
    }
  }

  private function updateSite($site)
  {
    JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_installer/models', 'InstallerModel');
    $input = JFactory::getApplication('site')->input;
    $input->set('installtype', 'folder');

    $model = JModelLegacy::getInstance('default', 'JoomlaupdateModel');

    $plugins = glob(__DIR__ . "/MultiSiteUpdates/Extensions/*");

    foreach ($plugins as $path) {
      $name = basename($path);

      $input->set('install_directory', $path);
      $model = JModelLegacy::getInstance('Install', 'InstallerModel', array('ignore_request' => true));
      if ($model->install()) {
        $this->out('--updating ' . $name . ' on ' . $site['db']);
      } else {
        $this->out('--error updating ' . $name . ' on ' . $site['db']);
      }
    }
  }
}

// and use chaining to execute the application.
JApplicationCli::getInstance('UpdatePluginsCli')->execute();