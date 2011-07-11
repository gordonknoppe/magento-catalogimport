<?php
/**
 * Guidance Catalogimport
 *
 * Rewrite of Varien_File_Uploader::getnewFileName to eliminate filename version incrementing,
 * i.e. file.jpg, file_1.jpg, etc.
 *
 * @package     Guidance
 * @subpackage  Guidance_Catalogimport
 * @copyright   Copyright (c) 2010 Guidance Solutions, Inc. (http://www.guidance.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Chris Lohman <clohm@guidance.com>
 */

class Guidance_Catalogimport_Model_File_Uploader extends Varien_File_Uploader
{
    static public function getNewFileName($destFile)
    {
        $fileInfo = pathinfo($destFile);
        return $fileInfo['basename'];
    }
}