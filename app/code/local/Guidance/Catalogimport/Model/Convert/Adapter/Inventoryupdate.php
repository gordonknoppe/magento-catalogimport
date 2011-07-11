<?php
/**
 * Guidance Catalogimport
 *
 *
 * @package     Guidance
 * @subpackage  Guidance_Catalogimport
 * @copyright   Copyright (c) 2010 Guidance Solutions, Inc. (http://www.guidance.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Guidance_Catalogimport_Model_Convert_Adapter_Inventoryupdate
    extends Mage_Eav_Model_Convert_Adapter_Entity
{
    public function finish()
    {
        // Finish function gets called after at the end of the advance profile run
        // See Mage_Dataflow_Model_Batch::beforeFinish().
        //need to refresh the native inventory indexing
        Mage::getSingleton('index/indexer')->getProcessByCode('cataloginventory_stock')->reindexEverything();

    }

    public function parse()
    {
        $batchModel = Mage::getSingleton('dataflow/batch');
        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds = $batchImportModel->getIdCollection();

        foreach ($importIds as $importId) {
            //print '<pre>'.memory_get_usage().'</pre>';
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();

            $this->saveRow($importData);
        }
    }

    /**
     * Save inventory (import)
     *
     * @param array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
        $model = '';

        //$website_id = trim($importData['website']);
        $sku = trim($importData['sku']);
        $qty = trim($importData['qty']);

        if($qty == '')
        {
            $message = Mage::helper('catalog')->__('Empty quantity, skipping row.');
            Mage::throwException($message);
        }

        $product = Mage::getModel('catalog/product');
        $productId = $product->getIdBySku($sku);
        $statusAttribute = $product->getResource()->getAttribute('status');
        //if ($website_id)
        //{
            //$website = Mage::app()->getWebsite($website_id);

            $inventoryModel = Mage::getModel('cataloginventory/stock_item');
            $inventoryModel->loadByProduct($productId);

            $tableName = $product->getResource()->getEntityTable();
            if ($statusAttribute->getBackendType()!='static') {
                 $tableName .= '_'.$statusAttribute->getBackendType();
            }

            $inventoryModel->setQty($qty);

            $stockQty = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY);

            if($qty>$stockQty)
            {
               $inventoryModel->setIsInStock(1);
            }
            else
            {
               $inventoryModel->setIsInStock(0);
            }
            
            try {
                $inventoryModel->save();
            } catch (Exception $e) {
                $errorStr = $e->getMessage()."\n";
                Mage::throwException($errorStr);
            }

            $message = Mage::helper('dataflow')->__("Sku: ".$sku." updated with new qty: ".$qty);
            Mage::throwException($message);
        //}
    }
}