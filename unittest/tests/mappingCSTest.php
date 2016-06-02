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

use testing\unittest\unittestdatabase;

/**
 * Test cs mapping functions
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class mappingcstest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "plugin_csmodule_mapping" . DIRECTORY_SEPARATOR . "unittest" . DIRECTORY_SEPARATOR  . "fixtures" . DIRECTORY_SEPARATOR . "mapping.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "plugin_csmodule_mapping" . DIRECTORY_SEPARATOR . "unittest" . DIRECTORY_SEPARATOR  . "fixtures" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test get mapping function - get UK saturn code
     * @group mapping
     */
    public function test_get_mapping_saturn() {
        $mapping = $this->getMockBuilder('plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db))
            ->getMock();
        $mapping->expects($this->once())
            ->method('callws')
            ->will($this->returnValue('G51MCS-UK'));
        // UK code.
        $this->assertEquals("G51MCS", $mapping->get_mapping("COMP1007"));
    }
    /**
     * Test get mapping function - get MY saturn code
     * @group mapping
     */
    public function test_get_mapping_saturn_my() {
        $mapping = $this->getMockBuilder('plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db))
            ->getMock();
        // Malaysia code.
        $mapping->expects($this->once())
            ->method('callws')
            ->will($this->returnValue('G51MCS-MY'));
        $this->assertEquals("G51MCS_UNMC", $mapping->get_mapping("COMP1019_UNMC"));
    }
    /**
     * Test get mapping function - get CN saturn code
     * @group mapping
     */
    public function test_get_mapping_saturn_cn() {
        $mapping = $this->getMockBuilder('plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db))
            ->getMock();
        // China code.
        $mapping->expects($this->once())
            ->method('callws')
            ->will($this->returnValue('G51MCS-CN'));
        $this->assertEquals("G51MCS_UNNC", $mapping->get_mapping("COMP1036_UNNC"));
    }
    /**
     * Test get mapping function - unrecognised code
     * @group mapping
     */
    public function test_get_mapping_unknown() {
        // webs ervice should not be called.
        $mapping = $this->getMockBuilder('plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db))
            ->getMock();
        // Un-recognised code.
        $mapping->expects($this->never())
            ->method('callws')
            ->will($this->returnValue('TEST'));
        $this->assertEquals("TEST", $mapping->get_mapping("TEST"));
    }
    /**
     * Test install mapping plugin - already installed on setup
     * @group mapping
     */
    public function test_install() {
        $mapping = new plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping($this->db);
        $this->assertEquals('OK', $mapping->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $queryTable = $this->getConnection()->createQueryTable('plugins', 'SELECT component, version, type FROM plugins');
        $expectedTable = $this->get_expected_data_set('pluginconfig')->getTable("plugins");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT * FROM config order by 1, 2');
        $expectedTable = $this->get_expected_data_set('pluginconfig')->getTable("config");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $mapping->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    }
    /**
     * Test uninstall mapping plugin - already installed on setup
     * @group mapping
     */
    public function test_uninstall() {
        $mapping = new plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping($this->db);
        $mapping->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $this->assertEquals('OK', $mapping->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $queryTable = $this->getConnection()->getRowCount('plugins');
        $this->assertEquals(0, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT * FROM config  order by 1, 2');
        $expectedTable = $this->get_expected_data_set('nopluginconfig')->getTable("config");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test check plugin version
     * @group mapping
     */
    public function test_get_plugin_version() {
        $mapping = new plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping($this->db);
        $mapping->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $this->assertEquals('1.0.0', $mapping->get_plugin_version('plugin_csmodule_mapping'));
        $mapping->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    }
    /**
     * Test get plugin version from file
     * @group mapping
     */
    public function test_get_file_version() {
        $mapping = new plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping($this->db);
        $this->assertEquals('1.0.0', $mapping->get_file_version());
    }
    /**
     * Test get plugin requires from file
     * @group mapping
     */
    public function test_get_file_requires() {
        $mapping = new plugins\mapping\plugin_csmodule_mapping\plugin_csmodule_mapping($this->db);
        $this->assertEquals('6.1.0', $mapping->get_file_requires());
    }
}
