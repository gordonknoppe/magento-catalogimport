<?php
/**
 * Guidance Catalogimport
 *
 * Modified catalog product media gallery attribute backend model with option to overwrite existing image.
 *
 * @package     Guidance
 * @subpackage  Guidance_Catalogimport
 * @copyright   Copyright (c) 2010 Guidance Solutions, Inc. (http://www.guidance.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Chris Lohman <clohm@guidance.com>
 */

class Guidance_Catalogimport_Model_Product_Attribute_Backend_Media extends Mage_Catalog_Model_Product_Attribute_Backend_Media
{

    /**
     * Add image to media gallery and return new filename
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string                     $file              file path of image in file system
     * @param string|array               $mediaAttribute    code of attribute with type 'media_image',
     *                                                      leave blank if image should be only in gallery
     * @param boolean                    $move              if true, it will move source file
     * @param boolean                    $exclude           mark image as disabled in product page view
     * @return string
     */
    public function addImage(Mage_Catalog_Model_Product $product, $file, $mediaAttribute=null, $move=false, $exclude=true)
    {
        $file = realpath($file);

        if (!$file || !file_exists($file)) {
            Mage::throwException(Mage::helper('catalog')->__('Image not exists'));
        }
        $pathinfo = pathinfo($file);
        if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), array('jpg','jpeg','gif','png'))) {
            Mage::throwException(Mage::helper('catalog')->__('Invalid image file type'));
        }

        $fileName       = Varien_File_Uploader::getCorrectFileName($pathinfo['basename']);
        $dispretionPath = Varien_File_Uploader::getDispretionPath($fileName);
        $fileName       = $dispretionPath . DS . $fileName;

        $fileName = $dispretionPath . DS
                  . Guidance_Catalogimport_Model_File_Uploader::getNewFileName($this->_getConfig()->getTmpMediaPath($fileName));

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->setAllowCreateFolders(true);
        $distanationDirectory = dirname($this->_getConfig()->getTmpMediaPath($fileName));

        try {
            $ioAdapter->open(array(
                'path'=>$distanationDirectory
            ));

            if ($move) {
                $ioAdapter->mv($file, $this->_getConfig()->getTmpMediaPath($fileName));
            } else {
                //var_dump($this->_getConfig()->getTmpMediaPath($fileName));
		$ioAdapter->cp($file, $this->_getConfig()->getTmpMediaPath($fileName));
                $ioAdapter->chmod($this->_getConfig()->getTmpMediaPath($fileName), 0777);
            }
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('catalog')->__('Failed to move file: %s', $e->getMessage()));
        }

        $fileName = str_replace(DS, '/', $fileName);

        $attrCode = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $product->getData($attrCode);
        $position = 0;
        if (!is_array($mediaGalleryData)) {
            $mediaGalleryData = array(
                'images' => array()
            );
        }

        foreach ($mediaGalleryData['images'] as &$image) {
            if (isset($image['position']) && $image['position'] > $position) {
                $position = $image['position'];
            }
        }

        $position++;
	// Chris Lohman - if label attribute is set, let's save it.
	$label = isset($mediaAttribute['label']) ? $mediaAttribute['label'] : '';

        $mediaGalleryData['images'][] = array(
            'file'     => $fileName,
            'position' => $position,
            'label'    => $label,
            'disabled' => (int) $exclude
        );
        $product->setData($attrCode, $mediaGalleryData);

        if (!is_null($mediaAttribute)) {
            $this->setMediaAttribute($product, $mediaAttribute, $fileName);
        }

        return $fileName;
    }

    protected function _moveImageFromTmp($file)
    {
        $ioObject = new Varien_Io_File();
        $destDirectory = dirname($this->_getConfig()->getMediaPath($file));
        try {
            $ioObject->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $ioObject->mkdir($destDirectory, 0777, true);
            $ioObject->open(array('path'=>$destDirectory));
        }

        if (strrpos($file, '.tmp') == strlen($file)-4) {
            $file = substr($file, 0, strlen($file)-4);
        }

        $destFile = dirname($file) . $ioObject->dirsep()
                  . Guidance_Catalogimport_Model_File_Uploader::getNewFileName($this->_getConfig()->getMediaPath($file));
        $ioObject->mv(
            $this->_getConfig()->getTmpMediaPath($file),
            $this->_getConfig()->getMediaPath($destFile)
        );

        return str_replace($ioObject->dirsep(), '/', $destFile);
    }

    protected function _copyImage($file)
    {
        try {
            $ioObject = new Varien_Io_File();
            $destDirectory = dirname($this->_getConfig()->getMediaPath($file));
            $ioObject->open(array('path'=>$destDirectory));
            $destFile = dirname($file) . $ioObject->dirsep()
                      . Guidance_Catalogimport_Model_File_Uploader::getNewFileName($this->_getConfig()->getMediaPath($file));

            if (!$ioObject->fileExists($this->_getConfig()->getMediaPath($file),true)) {
                throw new Exception();
            }
            $ioObject->cp(
                $this->_getConfig()->getMediaPath($file),
                $this->_getConfig()->getMediaPath($destFile)
            );
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('catalog')->__('Failed to copy file %s. Please, delete media with non-existing images and try again.',
                    $this->_getConfig()->getMediaPath($file))
            );
        }

        return str_replace($ioObject->dirsep(), '/', $destFile);
    }
}