<?php
/**
 * Guidance Catalogimport
 *
 * Adapter for importing categories. 
 *
 * @package     Guidance
 * @subpackage  Guidance_Catalogimport
 * @copyright   Copyright (c) 2010 Guidance Solutions, Inc. (http://www.guidance.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Guidance_Catalogimport_Model_Convert_Adapter_Category
    extends Mage_Eav_Model_Convert_Adapter_Entity
{
    protected $_categoryCache = array();
    
    protected $_stores;

    /**
     * Category display modes
     */
    protected $_displayModes = array( 'PRODUCTS', 'PAGE', 'PRODUCTS_AND_PAGE');

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
     * Save category (import)
     *
     * @param array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
	if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'store');
                Mage::throwException($message);
            }
        } else {
	    $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists', $importData['store']);
            Mage::throwException($message);
        }

        $rootId = $store->getRootCategoryId();

	if (!$rootId) {
            return array();
        }
        $rootPath = '1/'.$rootId;
        if (empty($this->_categoryCache[$store->getId()])) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStore($store)
                ->addAttributeToSelect('name');
            $collection->getSelect()->where("path like '".$rootPath."/%'");

            foreach ($collection as $cat) {
                $pathArr = explode('/', $cat->getPath());
                $namePath = '';
                for ($i=2, $l=sizeof($pathArr); $i<$l; $i++) {
                    $name = $collection->getItemById($pathArr[$i])->getName();
                    $namePath .= (empty($namePath) ? '' : '/').trim($name);
                }
                $cat->setNamePath($namePath);
            }

            $cache = array();
            foreach ($collection as $cat) {
                $cache[strtolower($cat->getNamePath())] = $cat;
                $cat->unsNamePath();
            }
            $this->_categoryCache[$store->getId()] = $cache;
        }
        $cache =& $this->_categoryCache[$store->getId()];

        $importData['categories'] = preg_replace('#\s*/\s*#', '/', trim($importData['categories']));
        if (!empty($cache[$importData['categories']])) {
            return true;
        }

        $path = $rootPath;
        $namePath = '';

        $i = 1;
        $categories = explode('/', $importData['categories']);

	foreach ($categories as $catName) {
            $namePath .= (empty($namePath) ? '' : '/').strtolower($catName);
            if (empty($cache[$namePath])) {

                $dispMode = $this->_displayModes[2];

                $cat = Mage::getModel('catalog/category')
                    ->setStoreId($store->getId())
                    ->setPath($path)
                    ->setName($catName)
                    ->setIsActive(1)
                    ->setIsAnchor(1)
                    ->setDisplayMode($dispMode)
                    ->save();
		$cache[$namePath] = $cat;
            }
            $catId = $cache[$namePath]->getId();
            $path .= '/'.$catId;
            $i++;
        }

        return true;
    }

    /**
     *  Init stores
     */
    protected function _initStores ()
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores(true, true);
            foreach ($this->_stores as $code => $store) {
                $this->_storesIdCode[$store->getId()] = $code;
            }
        }
    }

    /**
     * Retrieve store object by code
     *
     * @param string $store
     * @return Mage_Core_Model_Store
     */
    public function getStoreByCode($store)
    {
        $this->_initStores();
        /**
         * In single store mode all data should be saved as default
         */
        if (Mage::app()->isSingleStoreMode()) {
            //return Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID); Chris Lohman
        }

        if (isset($this->_stores[$store])) {
            return $this->_stores[$store];
        }
        return false;
    }

    /**
     * Retrieve store object by code
     *
     * @param string $store
     * @return Mage_Core_Model_Store
     */
    public function getStoreById($id)
    {
        $this->_initStores();
        /**
         * In single store mode all data should be saved as default
         */
        if (Mage::app()->isSingleStoreMode()) {
            // comment out b/c always returning 0
	    //return Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        }

        if (isset($this->_storesIdCode[$id])) {
            return $this->getStoreByCode($this->_storesIdCode[$id]);
        }
        return false;
    }

}