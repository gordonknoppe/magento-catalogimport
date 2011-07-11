<?php
/**
 * Guidance Catalogimport
 *
 * A customer product adapter that adds advanced functionality to the base Magento behavior.
 *
 * Some additional features provided:
 *
 * - Ability to import configurable and grouped products.
 * - Associate simple products to configurables.
 * - Assign attributes for configurable products to be configurable upon.
 * - Use "category_keys" to assign product categories.
 * - Ability to override and clear product images in the media gallery.
 *
 *
 * @package     Guidance
 * @subpackage  Guidance_Catalogimport
 * @copyright   Copyright (c) 2010 Guidance Solutions, Inc. (http://www.guidance.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Chris Lohman <clohm@guidance.com>
 */

/*
 Override product import saveRow function to add functionality to:
 - Associate simple product to the parent if parent_sku field is in CSV
 - Assign attributes for configurable products to be configurable on
 - Use eav value "category keys" to assign product categories
 Written By: Chris Lohman
 Date: 7/3/2010
 */
class Guidance_Catalogimport_Model_Convert_Adapter_Product
extends Mage_Catalog_Model_Convert_Adapter_Product
{

    /**
     * Save product (import)
     *
     * @param array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
        $product = $this->getProductModel()->reset();

        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'store');
                Mage::throwException($message);
            }
        }
        else {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists', $importData['store']);
            Mage::throwException($message);
        }

        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
        $productId = $product->getIdBySku($importData['sku']);

        if ($productId) {
            $product->load($productId);
        }
        else {
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();

            /**
             * Check product define type
             */
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'type');
                Mage::throwException($message);
            }
            $product->setTypeId($productTypes[strtolower($importData['type'])]);

            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'attribute_set');
                Mage::throwException($message);
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__('Skip import row, required field "%s" for new products not defined', $field);
                    Mage::throwException($message);
                }
            }
        }

        $this->setProductTypeInstance($product);

        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }

        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }

        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            $websiteCodes = explode(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                }
                catch (Exception $e) {}
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }

        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            if (in_array($field, $this->_imageFields)) {
                continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray = false;
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                }
                else {
                    $setValue = null;
                    foreach ($options as $item) {
                        if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }

            $product->setData($field, $setValue);
        }

        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }

        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
        ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
        : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                }
                else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);


        //Customized: Remove all gallery images if 'remove_all_images' set to true in import data.
        //This core code has been updated. Customization may not be necessary anymore.
        if(isset($importData['remove_all_images'])
        && ($importData['remove_all_images']=="yes" || $importData['remove_all_images']=="1")) {
            $attributes = $product->getTypeInstance()->getSetAttributes();
            if (isset($attributes['media_gallery'])) {
                $gallery = $attributes['media_gallery'];
                $galleryData = $product->getMediaGallery();
                foreach($galleryData['images'] as $image){
                    if ($gallery->getBackend()->getImage($product, $image['file'])) {
                        $gallery->getBackend()->removeImage($product, $image['file']);
                    }
                }
            }
        }

        $imageData = array();
        foreach ($this->_imageFields as $field) {
            if (!empty($importData[$field]) && $importData[$field] != 'no_selection') {
                if (!isset($imageData[$importData[$field]])) {
                    $imageData[$importData[$field]] = array();
                }
                $imageData[$importData[$field]][] = $field;
            }
        }
        $imageData = array_reverse($imageData,true);
        foreach ($imageData as $file => $fields) {
            try {
                // Chris Lohman - add label data to fields for saving later.
                $fields['label'] = $importData['label'];
                $product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $file, $fields, false, false);
            }
            catch (Exception $e) {}
        }

        /* Begin Guidance custom code */


        //CUSTOM: Convert Category Keys to ids and re-set category ids in Product model
        if (isset($importData['category_keys'])) {
            if (is_string($importData['category_keys'])) {
                $keys = explode(',', $importData['category_keys']);
            } else {
                Mage::throwException(Mage::helper('catalog')->__('Invalid Category Keys'));
            }

            foreach ($keys as $i=>$v) {
                $keys[$i] = trim($v);
            }

            $cat_model = Mage::getModel('catalog/category');
            $ids = array();
            foreach($keys as $key)
            {
                $category = $cat_model->loadByAttribute('category_key',$key);
                if(!$category) {
                    Mage::throwException(Mage::helper('catalog')->__('Failed to find valid Category matching key: '.$key));
                } else {
                    $ids[] = $category->getId();
                }
            }
            $product->setCategoryIds($ids);
        }

        /* End Guidance custom code */

        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

        $product->save();

        /* Start Guidance custom code part 2.
         * In order to do the next 2 operations, we need to have a valid product id,
         * which gets generated by $product->save().
         */

        /* Set the configurable product up with the attributes it's configurable on - i.e. "size" */
        if (isset($importData['configurable_on']))
        {
            if (is_string($importData['configurable_on'])) {
                $codes = explode(',', $importData['configurable_on']);
            } else {
                Mage::throwException(Mage::helper('catalog')->__('Invalid attribute_code used in configurable_on column'));
            }

            if (count($codes) && $product->isConfigurable()	&& !($product->getTypeInstance()->getUsedProductAttributeIds()))
            {
                foreach($codes as $code)
                {
                    // Add some safety in case import has spaces.
                    $code = trim($code);

                    $attr_id = $this->getProductModel()->getResource()->getAttribute($code)->getIdByCode('catalog_product', $code);
                    $attr_label = $this->getProductModel()->getResource()->getAttribute($code)->getFrontend()->getLabel();

                    $res = Mage::getSingleton('core/resource');
                    $w = $res->getConnection('core_write');

                    $table = $res->getTableName('catalog/product_super_attribute');

                    $productId = $product->getId();

                    $w->insert($table, array(
				   'product_id'    => $productId,
				   'attribute_id'     => $attr_id
                    ));

                    $super_attribute_id = $w->lastInsertId();

                    $table = $res->getTableName('catalog/product_super_attribute_label');
                    $w->insert($table, array(
				   'product_super_attribute_id'    => $super_attribute_id,
				   'value'     => $attr_label
                    ));

                }
            }

            /* Associate children to configurable products, if defined in import. */
            if($importData['child_sku']) {
                $childSKUs = array();
                $childIds = array();
                if(is_string($importData['child_sku'])){
                    $childSKUs = preg_split("/[\s]*[,]+[\s]*/", $importData['child_sku']);
                } else {
                    Mage::throwException(Mage::helper('catalog')->__('Invalid attribute_code used in child_sku column'));
                }

                if(is_array($childSKUs)) {
                    foreach($childSKUs as $childSKU){
                        $childIds[] = $product->getIdBySku($childSKU);
                    }
                    Mage::getResourceModel('catalog/product_type_configurable')
                        ->saveProducts($product, $childIds);
                } else {
                    Mage::throwException(Mage::helper('catalog')->__('Invalid child products data'));
                }
            }
        }
        
        /* Take the parent_sku value from simple product import and use it to assign this simple product to configurable product identified by parent_sku */
        if (isset($importData['parent_sku'])) {

            $productId = $product->getId();
            $parentId = $product->getIdBySku($importData['parent_sku']);
            $parent = Mage::getModel('catalog/product')->load($parentId);

            /*Mage::log('Guidance_Catalogimport | Found parent_sku ------------------');
             Mage::log('productId: ' . $productId);
             Mage::log('parentId: ' . $parentId);
             Mage::log('$parent->isGrouped(): ' . $parent->isGrouped());
             Mage::log('$parent->getTypeId(): ' . $parent->getTypeId());*/

            /**
             * Expecting fields from import file:
             * parent_sku, [group_order], [group_qty]
             */
            if ($parent->isGrouped())
            {
                $group_qty = '';
                $group_order = '';
                if (isset($importData['group_qty']) && is_numeric($importData['group_qty'])) {
                    $group_qty = $importData['group_qty'];
                }
                if (isset($importData['group_order']) && is_numeric($importData['group_order'])) {
                    $group_order = $importData['group_order'];
                }

                $linkData = $this->getGroupedProductsArray($parent);
                $linkData[$productId] = array(
                'qty' => $group_qty,
                'position' => $group_order,
                );
                $parent->setGroupedLinkData($linkData);
                $parent->save();
            }
            else
            {
                $res = Mage::getSingleton('core/resource');
                $w = $res->getConnection('core_write');
                $table = $res->getTableName('catalog/product_super_link');  // this is the db table that stores the parent/child relp.
                $select = $w->select()
                ->from($table, '*')
                ->where('product_id=?', $productId)
                ->where('parent_id=?', $parentId);
                $matches = $w->fetchAssoc($select);

                // if no matches, then insert row to associate child with parent
                if(!count($matches))
                {
                    $w->insert($table, array(
                   'product_id'    => $productId,
                   'parent_id'     => $parentId
                    ));
                }



                /* -------------------------------------------- */
                $res = Mage::getSingleton('core/resource');
                $w = $res->getConnection('core_write');

                $table = $res->getTableName('catalog/product_relation');  // this is the db table that stores the parent/child relp.

                $productId = $product->getId();
                $parentId = $product->getIdBySku($importData['parent_sku']);

                $select = $w->select()
                ->from($table, '*')
                ->where('child_id=?', $productId)
                ->where('parent_id=?', $parentId);
                $matches = $w->fetchAssoc($select);

                // if no matches, then insert row to associate child with parent
                if(!count($matches))
                {
                    $w->insert($table, array(
                   'child_id'    => $productId,
                   'parent_id'     => $parentId
                    ));
                }
            }
        }

        /* End Guidance custom code part 2 */

        return true;

    }


    /**
     * Retrieve grouped products
     * @see Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group::getSelectedGroupedProducts()
     * @return array
     */
    public function getGroupedProductsArray($product)
    {
        $associatedProducts = $product->getTypeInstance(true)
        ->getAssociatedProducts($product);
        $products = array();
        foreach ($associatedProducts as $associatedProduct) {
            $products[$associatedProduct->getId()] = array(
                'qty'       => $associatedProduct->getQty(),
                'position'  => $associatedProduct->getPosition()
            );
        }
        return $products;
    }


}
