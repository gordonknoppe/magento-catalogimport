<?php
/**
 * Guidance Catalogimport
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @package     Guidance
 * @subpackage  Guidance_Catalogimport
 * @copyright   Copyright (c) 2010 Guidance Solutions, Inc. (http://www.guidance.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Chris Lohman <clohm@guidance.com>
 */

Mage::log("START INSTALL IMPORTEXPORT SQL");
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$connection = $installer->getConnection();
/* @var $connection Varien_Db_Adapter_Pdo_Mysql */

$installer->startSetup();

$connection->insert($installer->getTable('dataflow/profile'), array(
	'profile_id' => NULL, 
	'name' => 'Update Inventory', 
	'created_at' => date('Y-m-d h:i:s'), 
	'updated_at' => date('Y-m-d h:i:s'), 
	'actions_xml' =>
'<action type="dataflow/convert_adapter_io" method="load">
    <var name="type">file</var>
    <var name="path">var/import</var>
    <var name="filename"><![CDATA[Inventoryupdate.csv]]></var>
    <var name="format"><![CDATA[csv]]></var>
</action>

<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="map">
        <map name="column1"><![CDATA[sku]]></map>
        <map name="column2"><![CDATA[qty]]></map>
    </var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">catalogimport/convert_adapter_inventoryupdate</var>
    <var name="method">parse</var>
</action>', 
	'gui_data' => '', 
	'direction' => NULL, 
	'entity_type' => '', 
	'store_id' => '0', 
	'data_transfer' => NULL
));

$installer->endSetup();																																																				 
	