<?php
/**
 * Guidance Catalogimport
 *
 * Catalogimport index controller. Not used.
 *
 * @package     Guidance
 * @subpackage  Guidance_Catalogimport
 * @copyright   Copyright (c) 2010 Guidance Solutions, Inc. (http://www.guidance.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Guidance_Catalogimport_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/catalogimport?id=15 
    	 *  or
    	 * http://site.com/catalogimport/id/15 	
    	 */
    	/* 
		$catalogimport_id = $this->getRequest()->getParam('id');

  		if($catalogimport_id != null && $catalogimport_id != '')	{
			$catalogimport = Mage::getModel('catalogimport/catalogimport')->load($catalogimport_id)->getData();
		} else {
			$catalogimport = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($catalogimport == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$catalogimportTable = $resource->getTableName('catalogimport');
			
			$select = $read->select()
			   ->from($catalogimportTable,array('catalogimport_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$catalogimport = $read->fetchRow($select);
		}
		Mage::register('catalogimport', $catalogimport);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}
