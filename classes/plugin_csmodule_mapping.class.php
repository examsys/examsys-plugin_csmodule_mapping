<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

namespace plugins\mapping\plugin_csmodule_mapping;

/**
* Mapping plugin helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Module mapping plugin.
 */
class plugin_csmodule_mapping extends \plugins\plugins_mapping {
    /**
     * Name of the plugin;
     * @var string
     */
    protected $plugin = 'plugin_csmodule_mapping';
    /**
     * Language pack component.
     */
    private $langcomponent = 'plugins/mapping/plugin_csmodule_mapping/plugin_csmodule_mapping';
    /**
     * Call web service to retrieve mapping information.
     * @param string $source source module code
     * @return string target module code
     */
    private function callws($source) {
        $langpack = new \langpack();
        $strings = $langpack->get_all_strings($this->langcomponent);
        $url = $this->config->get_setting($this->plugin, 'url');
        $data = array('isconnectedquery' => $this->config->get_setting($this->plugin, 'isconnectedquery'),
                'maxrows' => $this->config->get_setting($this->plugin, 'maxrows'),
                'prompt_uniquepromptname' => $this->config->get_setting($this->plugin, 'prompt_uniquepromptname'),
                'prompt_fieldvalue' => $source,
                'filterfields' => $this->config->get_setting($this->plugin, 'filterfields'));
        // Add parameters onto GET request.
        foreach ($data as $param => $value) {
            $url .= $param . '=' . $value . '&';
        }
        // Strip last &.
        $url = substr($url, 0, -1);
        $username = $this->config->get_setting($this->plugin, 'username');
        $password = $this->config->get_setting($this->plugin, 'password');
        $timeout = $this->config->get_setting($this->plugin, 'timeout');
        $restful = new \restful($this->db);
        $response = $restful->get($url, $timeout, $username, $password);
        if ($response == '') {
            return $source;
        }
        // Parse returned XML.
        $data = new \DOMDocument();
        $data->loadXML($response);
        // Check data returned. Return source code if no data.
        $datanode = $data->getElementsByTagName('data')->item(0);
        if (!$datanode->hasChildNodes()) {
            $log = new \Logger($this->db);
            $userObj = $userObject = \UserObject::get_instance();
            $userid = $userObj->get_user_ID();
            $username = $userObj->get_username();
            $errorfile = $_SERVER['PHP_SELF'];
            $errorline = __LINE__ - 5;
            $log->record_application_warning($userid, $username, $strings['restnodata'], $errorfile, $errorline);
            return $source;
        }
        $items = $data->getElementsByTagName('currentRow');
        $row = array();
        foreach ($items as $item) {
            foreach ($item->childNodes as $childnode) {
                $row[] = $childnode->nodeValue;
            }
        }
        return $row[0];
    }
    /**
     * Getting saturn<->campus module mapping
     * @param string $source source module code
     * @return string orginial $source if mapping not found, $target if mapping found
     */
    public function get_mapping($source) {
        // Check if source is campus solutions.
        preg_match("/^(?P<module>[A-Z]{4}[F1-5][0-9]{3})(_(?P<country>UNNC|UNMC))?$/", $source, $info);
        if (count($info) > 0) {
            // Campus expects country to be supplied so if not use UNUK.
            $source = $info['module'];
            if (!isset($info['country'])) {
                $source .= '-UNUK';
            }
        } else {
            // Check is source is saturn.
            preg_match("/^(?P<module>[A-Z0-9]{6})(_(?P<country>UNNC|UNMC))?$/", $source, $info);
            if (count($info) > 0) {
                // Campus expect saturn code to use 2 character campus code. i.e. CN not UNNC
                $source = $info['module'];
                if (!isset($info['country']) or $info['country'] == 'UNUK') {
                    $source .= '-UK';
                } elseif ($info['country'] == 'UNNC') {
                    $source .= '-CN';
                } elseif ($info['country'] == 'UNMC') {
                    $source .= '-MY';
                }
            } else {
                // Return source module id if naming convention not recognised.
                return $source;
            }
        }
        // Call web service.
        $target = $this->callws($source);
        // CS country mapping.
        preg_match("/^(?P<module>[A-Z]{4}[F1-5][0-9]{3})-(?P<country>UNUK|UNNC|UNMC)$/", $target, $info);
        if (count($info) > 0) {
            if (!isset($info['country']) or $info['country'] == 'UNUK') {
                $target = $info['module'];
            } elseif ($info['country'] == 'UNNC') {
                $target = $info['module'] . $info['country'];
            } elseif ($info['country'] == 'UNMC') {
                $target = $info['module']. $info['country'];
            }
        } else {
            // Saturn Country mapping.
            preg_match("/^(?P<module>[A-Z0-9]{6})-(?P<country>UK|CN|MY)$/", $target, $info);
             if (count($info) > 0) {
                if (!isset($info['country']) or $info['country'] == 'UK') {
                    $target = $info['module'];
                } else {
                    $target = $info['module'] . $info['country'];
                }
            }
        }
        return $target;
    }
    
    /**
     * Enable this plugin
     * Only one module mapping plugin should be enabled at anyone time
     */
    public function enable_plugin() {
        $enabled = array($this->plugin);
        $this->config->set_setting('enabled_plugin', json_encode($enabled), 'plugin_' . $this->plugin_type);
    }
    /**
     * Disable this plugin
     * Only one module mapping plugin should be enabled at anyone time
     */
    public function disable_plugin() {
        $enabled = json_decode($this->config->get_setting('plugin_' . $this->plugin_type, 'enabled_plugin'));
        if ($this->plugin == $enabled[0]) {
            $this->config->set_setting('enabled_plugin', json_encode(array()), 'plugin_' . $this->plugin_type);
        }
    }
}