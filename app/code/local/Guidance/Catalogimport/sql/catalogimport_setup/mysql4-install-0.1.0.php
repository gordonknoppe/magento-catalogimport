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

$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
/* @var $connection Varien_Db_Adapter_Pdo_Mysql */

$connection->insert($installer->getTable('dataflow/profile'), array(
	'profile_id' => NULL, 
	'name' => 'Import Categories', 
	'created_at' => date('Y-m-d h:i:s'), 
	'updated_at' => date('Y-m-d h:i:s'), 
	'actions_xml' => '<action type="dataflow/convert_adapter_io" method="load">    
	<var name="type">file</var>    
	<var name="path">var/import</var>    
	<var name="filename"><![CDATA[Categories.csv]]></var>    
	<var name="format"><![CDATA[csv]]></var>
</action>

<action type="dataflow/convert_parser_csv" method="parse">
	<var name="delimiter"><![CDATA[,]]></var>
	<var name="enclose"><![CDATA["]]></var>
	<var name="fieldnames">true</var>
	<var name="store"><![CDATA[0]]></var>
	<var name="number_of_records">1</var>
	<var name="decimal_separator"><![CDATA[.]]></var>
	<var name="adapter">catalog/convert_adapter_category</var>
	<var name="method">parse</var>
</action>', 
	'gui_data' => NULL, 
	'direction' => NULL, 
	'entity_type' => '', 
	'store_id' => '0', 
	'data_transfer' => NULL
));

$bind = array(
	'profile_id' => NULL, 
	'name' => 'Guidance Add/Update Product Fields by Language', 
	'created_at' => date('Y-m-d h:i:s'), 
	'updated_at' => date('Y-m-d h:i:s'), 
	'actions_xml' => 
'<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames"></var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">catalog/convert_adapter_product</var>
    <var name="method">parse</var>
</action>',
	'gui_data' => 'a:7:{s:6:"export";a:1:{s:13:"add_url_field";s:1:"0";}s:6:"import";a:2:{s:17:"number_of_records";s:1:"1";s:17:"decimal_separator";s:1:".";}s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:18:"export_product.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:0:"";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}', 
	'direction' => 'import', 
	'entity_type' => 'product', 
	'store_id' => '0', 
	'data_transfer' => 'interactive'
);

$connection->insert($installer->getTable('dataflow/profile'), $bind);

$bind = array(
	'profile_id' => NULL, 
	'name' => 'Guidance Add/Update Product Images', 
	'created_at' => date('Y-m-d h:i:s'), 
	'updated_at' => date('Y-m-d h:i:s'), 
	'actions_xml' => 
'<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames"></var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">catalog/convert_adapter_product</var>
    <var name="method">parse</var>
</action>',
	'gui_data' => 'a:7:{s:6:"export";a:1:{s:13:"add_url_field";s:1:"0";}s:6:"import";a:2:{s:17:"number_of_records";s:1:"1";s:17:"decimal_separator";s:1:".";}s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:18:"export_product.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:0:"";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}', 
	'direction' => 'import', 
	'entity_type' => 'product', 
	'store_id' => '0', 
	'data_transfer' => 'interactive'
);

$connection->insert($installer->getTable('dataflow/profile'), $bind);

$bind = array(
	'profile_id' => NULL, 
	'name' => 'Guidance Add/Update Simple Products', 
	'created_at' => date('Y-m-d h:i:s'), 
	'updated_at' => date('Y-m-d h:i:s'), 
	'actions_xml' => 
'<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames"></var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">catalog/convert_adapter_product</var>
    <var name="method">parse</var>
</action>',
	'gui_data' => 'a:7:{s:6:"export";a:1:{s:13:"add_url_field";s:1:"0";}s:6:"import";a:2:{s:17:"number_of_records";s:1:"1";s:17:"decimal_separator";s:1:".";}s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:18:"export_product.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:0:"";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}', 
	'direction' => 'import', 
	'entity_type' => 'product', 
	'store_id' => '0', 
	'data_transfer' => 'interactive'
);

$connection->insert($installer->getTable('dataflow/profile'), $bind);

$bind = array(
	'profile_id' => NULL, 
	'name' => 'Guidance Add/Update Configurable Products', 
	'created_at' => date('Y-m-d h:i:s'), 
	'updated_at' => date('Y-m-d h:i:s'), 
	'actions_xml' => 
'<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames"></var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">catalog/convert_adapter_product</var>
    <var name="method">parse</var>
</action>',
	'gui_data' => 'a:7:{s:6:"export";a:1:{s:13:"add_url_field";s:1:"0";}s:6:"import";a:2:{s:17:"number_of_records";s:1:"1";s:17:"decimal_separator";s:1:".";}s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:18:"export_product.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:4:"true";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}', 
	'direction' => 'import', 
	'entity_type' => 'product', 
	'store_id' => '0', 
	'data_transfer' => 'interactive'
);

$connection->insert($installer->getTable('dataflow/profile'), $bind);

//$installer->run("    ");

$installer->endSetup(); 