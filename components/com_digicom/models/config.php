<?php
/**
* @package			DigiCom Joomla Extension
 * @author			themexpert.com
 * @version			$Revision: 341 $
 * @lastmodified	$LastChangedDate: 2013-10-10 14:28:28 +0200 (Thu, 10 Oct 2013) $
 * @copyright		Copyright (C) 2013 themexpert.com. All rights reserved.
* @license			GNU/GPLv3
*/

defined ('_JEXEC') or die ("Go away.");

jimport ("joomla.aplication.component.model");

class DigiComModelConfig extends DigiComModel
{
	
	var $_configs = null;
	var $_id = null;

	function __construct () {
		parent::__construct();
		$this->_id = 1;
	}

	function getConfigs() {
	
		$comInfo = JComponentHelper::getComponent('com_digicom');
		$this->_configs = $comInfo->params;
		
		$view = JRequest::getWord('view');
		$lay = JRequest::getWord('layout');

		if (strlen(trim($lay)) > 0) {
			if (strtolower(trim($view)) == "categories")
			switch(strtolower($lay)){
				case "list":
					$this->_configs->set('catlayoutstyle',0);
					break;
		
				case "listthumbs":
					$this->_configs->set('catlayoutstyle',1);
					break;
		
				case "dropdown":
					$this->_configs->set('catlayoutstyle',2);
					break;

			}
		}
		return $this->_configs;

	}


}