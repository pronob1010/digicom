<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_digicom
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_digicom
 *
 * @since  3.3
 */
class DigiComRouter extends JComponentRouterBase
{
	private $alias;
	
	function __construct(){
		$this->alias = new stdClass();

		parent::__construct();
	}
	/**
	 * Build the route for the com_digicom component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$menu = JMenu::getInstance('site');
		// print_r($query);die;
		$segments = array();
		//as query view is required to perform operation, so if we dont have them then return;
		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}
		// print_r($query);die;

		

		// Get a menu item based on Itemid or currently active
		$params = JComponentHelper::getParams('com_digicom');
		$advanced = $params->get('sef_advanced_link', 1);

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		
		if (empty($query['Itemid']))
		{
			// there are no menu Itemid found, lets dive into menu finder
			$menuItem = $menu->getItems('link', 'index.php?option=com_digicom&view='.$view, true);
			//print_r($menuItem);die;
			if(!is_null($menuItem) && count($menuItem)){
				$query['Itemid'] = $menuItem->id;
				$menuItemGiven = true;
			}else{
				$menuItem = $menu->getActive();
				if(!$menuItem){
					$menuItem = $menu->getDefault();
				}
				$menuItemGiven = false;
			}
		}
		else
		{
			// $menuItem = $menu->getItem($query['Itemid']);
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// we have menu item, Check again if its com_digicom
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_digicom')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		//lets check if its not in proper itemid
		if (
			($menuItem instanceof stdClass)
			&&
			$menuItem->query['view'] != $query['view']
		){
			// there are no exact menu Itemid found, lets dive into menu finder
			$checkmenu = JMenu::getInstance('site');
			$checkmenuItem = $checkmenu->getItem('link', 'index.php?option=com_digicom&view='.$view, true);

			if(!is_null($checkmenuItem)){
				$menu = $checkmenu;
				$menuItem = $checkmenuItem;
				$query['Itemid'] = $menuItem->id;
				$menuItemGiven = true;
			}elseif(!(isset($query['id']) && ($query['view'] == 'product' or $query['view'] == 'category')) ){
				$menuItem = $menu->getActive();
				if(!$menuItem){
					$menuItem = $menu->getDefault();
				}
				$menuItemGiven = false;
			}
		}

		// Are we dealing with an product or category that is attached to a menu item?
		if (($menuItem instanceof stdClass)
			&& $menuItem->query['view'] == $query['view']
			&& isset($query['id'])
			&& $menuItem->query['id'] == (int) $query['id'])
		{
			unset($query['view']);

			if (isset($query['catid']))
			{
				unset($query['catid']);
			}

			if (isset($query['layout']))
			{
				unset($query['layout']);
			}

			unset($query['id']);

			return $segments;
		}

		//print_r($segments);die;

		// lets handle view specific routing
		if ($view == 'orders' or $view == 'order')
		{
			unset($query['view']);

			if(!$menuItemGiven){
				// there are no menu Itemid found, lets dive into menu finder
				$menu = JMenu::getInstance('site');
				$menuItem = $menu->getItems('link', 'index.php?option=com_digicom&view=orders', true);

				if(!is_null($menuItem)){
					$query['Itemid'] = $menuItem->id;
					$menuItemGiven = true;
				}else{
					$menuItem = $menu->getActive();
					if(!$menuItem){
						$menuItem = $menu->getDefault();
					}
					$menuItemGiven = false;
				}
			}


			if(isset($query['layout'])){
				$segments[] = $query['layout'];
				unset($query['layout']);
			}
			if(isset($query['id'])){
				$segments[] = $query['id'];
				unset($query['id']);
			}

		}
		elseif ($view == 'dashboard' or $view == 'downloads' or $view == 'download' or $view == 'profile' or $view == 'login' or $view == 'register' or $view == 'billing' or $view == 'thankyou')
		{
			
			
			// now check if its downloads details view
			if($view == 'download' or $view == 'downloads'){
				// check for downloads menu
				$menuItem = $menu->getItems('link', 'index.php?option=com_digicom&view=downloads', true);
				
				// var_dump($menu);
				// die;

				if(!is_null($menuItem)) {
					$query['Itemid'] = $menuItem->id;
					$menuItemGiven = true;

					if($view == 'download'){
						$segments[] = $query['id'];
						unset($query['id']);
					}
				}
			}

			if (!$menuItemGiven)
			{
				$segments[] = $view;
			}

			unset($query['view']);

		}elseif ($view == 'checkout')	{
			if (!$menuItemGiven)
			{
				$segments[] = $view;

				// there are no menu Itemid found, lets dive into menu finder
				$menu = JMenu::getInstance('site');
				$menuItem = $menu->getItems('link', 'index.php?option=com_digicom&view=cart', true);

				//print_r($menuItem);die;
				if(!is_null($menuItem)){
					$query['Itemid'] = $menuItem->id;
					$menuItemGiven = true;
				}else{
					$menuItem = $menu->getActive();
					if(!$menuItem){
						$menuItem = $menu->getDefault();
					}
					$menuItemGiven = false;
				}
			}
			if(isset($query['id'])){
				$segments[] = $query['id'];
				unset($query['id']);
			}
			unset($query['view']);

		}
		elseif($view == 'cart')
		{
			$menu = JMenu::getInstance('site');
			$menuItem = $menu->getItems('link', 'index.php?option=com_digicom&view=cart', true);

			if (!$menuItemGiven)
			{
				if(!is_null($menuItem)){
					$query['Itemid'] = $menuItem->id;
					$menuItemGiven = true;
					unset($query['view']);
				}else{
					$segments[] = $view;
				}
			}else{
				unset($query['view']);
			}

		}
		// echo $view;die;
		// Handle product or category
		if ($view == 'category' || $view == 'product')
		{
			unset($query['view']);

			if ($view == 'product')
			{
				// print_r($query);die;
				// if (isset($query['id']) && isset($query['catid']) && $query['catid'])
				if (isset($query['id']))
				{
					$productid = $query['id'];
					// Make sure we have the id and the alias
					if (strpos($query['id'], ':') === false && !isset($this->alias->$productid))
					{
						$db = JFactory::getDbo();
						$dbQuery = $db->getQuery(true)
							->select('alias')
							->from('#__digicom_products')
							->where('id=' . (int) $query['id']);
						$db->setQuery($dbQuery);
						$this->alias->$productid = $db->loadResult();
						$query['id'] = $query['id'] . ':' . $this->alias->$productid;
					}elseif (strpos($query['id'], ':') === false && isset($this->alias->$productid)) {
						$query['id'] = $query['id'] . ':' . $this->alias->$productid;
					}
					else
					{
						$idarray = explode(":", $query['id']);
						$productid = $idarray[0];
					}

					if(isset($query['catid']) && $query['catid']){
						$catid = $query['catid'];
					}else{
						$db = JFactory::getDbo();
						$dbQuery = $db->getQuery(true)
							->select('catid')
							->from('#__digicom_products')
							->where('id=' . (int) $productid);
						$db->setQuery($dbQuery);
						$catid = $db->loadResult();
						$query['catid'] = $catid;
					}
				}
				else
				{
					// We should have these two set for this view.  If we don't, it is an error
					return $segments;
				}
			}
			else
			{
				if (isset($query['id']))
				{
					$catid = $query['id'];
				}
				else
				{
					// We should have id set for this view.  If we don't, it is an error
					return $segments;
				}
			}

			$categories = JCategories::getInstance('DigiCom');
			$category = $categories->get($catid);
			//print_r($category);die;
			if (!$category)
			{
				// We couldn't find the category we were given.  Bail.
				return $segments;
			}

			//-------------------------------------------
			if (!$menuItemGiven)
			{
				// there are no menu Itemid found, lets dive into menu finder
				$menu = JMenu::getInstance('site');
				$menuItem = $menu->getItems('link', 'index.php?option=com_digicom&view=category&id='.$catid, true);

				//print_r($menuItem);die;
				if(!is_null($menuItem) && count($menuItem))
				{
					$query['Itemid'] = $menuItem->id;
					$menuItemGiven = true;
				}else{
					$menuItem = $menu->getItems('link', 'index.php?option=com_digicom&view=category&id=0', true);
					//print_r($menuItem);die;
					if(!is_null($menuItem) && count($menuItem))
					{
						$query['Itemid'] = $menuItem->id;
						$menuItemGiven = true;
					}
				}


				if(!$menuItemGiven)
				{
					$menuItem = $menu->getActive();
					if(!$menuItem){
						$menuItem = $menu->getDefault();
					}
					$query['Itemid'] = $menuItem->id;
					$menuItemGiven = true;
				}

				//$segments[] = $view;
			}
			//-------------------------------------------
			// print_r($menuItemGiven);die;
			if ($menuItemGiven && isset($menuItem->query['id']))
			{
				$mCatid = $menuItem->query['id'];
			}
			else
			{
				$mCatid = 0;
			}

			$path = array_reverse($category->getPath());
			$array = array();

			foreach ($path as $id)
			{
				if ((int) $id == (int) $mCatid)
				{
					break;
				}

				list($tmp, $id) = explode(':', $id, 2);

				$array[] = $id;
			}

			// print_r($array);die;
			$array = array_reverse($array);

			if (!$advanced && !is_null($array))
			{
				$array[0] = (int) $catid . ':' . $array[0];
			}
			// print_r($query);die;
			$segments = array_merge($segments, $array);

			if ($view == 'product')
			{
				if ($advanced)
				{
					list($tmp, $id) = explode(':', $query['id'], 2);
				}
				else
				{
					$id = $query['id'];
				}

				$segments[] = $id;
			}

			// print_r($segments);die;
			unset($query['id']);
			unset($query['catid']);
		}

		/*if ($view == 'archive')
		{
			if (!$menuItemGiven)
			{
				$segments[] = $view;
				unset($query['view']);
			}

			if (isset($query['year']))
			{
				if ($menuItemGiven)
				{
					$segments[] = $query['year'];
					unset($query['year']);
				}
			}

			if (isset($query['year']) && isset($query['month']))
			{
				if ($menuItemGiven)
				{
					$segments[] = $query['month'];
					unset($query['month']);
				}
			}
		}*/


		/*
		 * If the layout is specified and it is the same as the layout in the menu item, we
		 * unset it so it doesn't go into the query string.
		 */
		if (isset($query['layout']))
		{
			if ($menuItemGiven && isset($menuItem->query['layout']))
			{
				if ($query['layout'] == $menuItem->query['layout'])
				{
					unset($query['layout']);
				}
			}
			elseif($view == 'cart' or $view == 'register'){
				if(!$menuItemGiven){
					unset($query['layout']);
				}else{
					$segments[] = $query['layout'];
					unset($query['layout']);
				}

			}
			else
			{
				if ($query['layout'] == 'default')
				{
					unset($query['layout']);
				}
			}
		}

		$total = !is_null($segments) ? count($segments) : 0;

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}
		// print_r($segments);die;
		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$menu = JMenu::getInstance('site');
		$total = !is_null($segments) ? count($segments) : 0;
		$vars = array();

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}



		// Get the active menu item.
		$item = $menu->getActive();

		
		if(!$item){
			$item = $menu->getDefault();
		}
		$params = JComponentHelper::getParams('com_digicom');
		$advanced = $params->get('sef_advanced_link', 1);
		$db = JFactory::getDbo();

		// Count route segments
		$count = !is_null($segments) ? count($segments) : 0;
		// we have menu item, Check again if its com_digicom
		
		/*
		 * Standard routing for products.  If we don't pick up an Itemid then we get the view from the segments
		 * the first segment is the view and the last segment is the id of the product or category.
		 */
		if (!isset($item))
		{
			//echo 4444;die;
			$vars['view'] = $segments[0];
			$vars['id'] = $segments[$count - 1];

			return $vars;
		}

		// lets deal with if not product or cat
		$tmpview 	= $item->query['view'];
		$option 	= $item->query['option'];
		if($option != 'com_digicom'){
			$vars['option'] = 'com_digicom';
			$vars['view'] = $segments[0];

			if(isset($segments[0])){
				$vars['view'] = $segments[0];
			}
			if(isset($segments[1])){
				$vars['id'] = $segments[1];
			}

			return $vars;
		}

		switch($tmpview){
			case "profile":
			case "dashboard":
			case "checkout":
			case "register":
			case "billing":
			case "thankyou":
				$vars['view'] = $item->query['view'];

				return $vars;
				break;
			case "download":
			case "downloads":
				$vars['view'] = $item->query['view'];
				if(isset($segments[0])  && is_numeric($segments[0])){
					$vars['view'] = 'download';
					$vars['id'] = $segments[0];
				}

				return $vars;
				break;
			case "orders":
			case "order":
				// print_r($segments);die;//checkout
				$vars['view'] = $item->query['view'];

				
				
				if(isset($segments[0]) && !is_numeric($segments[0]) && $segments[0] == 'invoice'){
					$vars['view'] = 'order';
					$vars['layout'] = $segments[0];
					$vars['id'] = $segments[1];
				}elseif(isset($segments[0]) && !is_numeric($segments[0]) && $segments[0] == 'checkout'){
					$vars['view'] = 'checkout';
					$vars['id'] = $segments[1];
				}elseif(isset($segments[0])  && is_numeric($segments[0])){
					$vars['view'] = 'order';
					$vars['id'] = $segments[0];
				}
				// print_r($vars);die;
				return $vars;
				break;
			case "cart":
				//print_r($segments);
				$vars['view'] = $item->query['view'];
				//print_r($vars);die;

				//if(isset($segments[0])){
				//$vars['layout'] = $segments[0];
				//}

				if(isset($segments[0]) && $segments[0] !='cart_popup' && $segments[0] !='summary'){
					$vars['view'] = $segments[0];
				}elseif(isset($segments[0])){
					$vars['layout'] = $segments[0];
				}

				if(isset($segments[1])  && is_numeric($segments[1])){
					$vars['id'] = $segments[1];
				}
					//print_r($vars);die;
					return $vars;
				break;
		}


		/*
		 * If there is only one segment, then it points to either an product or a category.
		 * We test it first to see if it is a category.  If the id and alias match a category,
		 * then we assume it is a category.  If they don't we assume it is an product
		 */
		if ($count == 1)
		{
			// We check to see if an alias is given.  If not, we assume it is an product
			if (!$advanced && strpos($segments[0], ':') === false)
			{
				$vars['view'] = 'product';
				$vars['id'] = (int) $segments[0];

				return $vars;
			}else{
				$vars['view'] = 'category';
				$vars['id'] = (int) $segments[0];
				$id = (int) $segments[0];
			}

			if(strpos($segments[0], ':') === true){
				list($id, $alias) = explode(':', $segments[0], 2);

				// First we check if it is a category
				$category = JCategories::getInstance('DigiCom')->get($id);

				if ($category && $category->alias == $alias)
				{
					$vars['view'] = 'category';
					$vars['id'] = $id;

					return $vars;
				}
			}else{
				$query = $db->getQuery(true)
					->select($db->quoteName(array('alias', 'catid')))
					->from($db->quoteName('#__digicom_products'))
					->where($db->quoteName('id') . ' = ' . (int) $id);
				$db->setQuery($query);
				$product = $db->loadObject();

				if ($product)
				{
					if ($product->alias == $alias)
					{
						$vars['view'] = 'product';
						$vars['catid'] = (int) $product->catid;
						$vars['id'] = (int) $id;

						return $vars;
					}
				}
			}
		}

		/*
		 * If there was more than one segment, then we can determine where the URL points to
		 * because the first segment will have the target category id prepended to it.  If the
		 * last segment has a number prepended, it is an product, otherwise, it is a category.
		 */
		if (!$advanced)
		{
			$cat_id = (int) $segments[0];

			$product_id = (int) $segments[$count - 1];

			if ($product_id > 0)
			{
				$vars['view'] = 'product';
				$vars['catid'] = $cat_id;
				$vars['id'] = $product_id;
			}
			else
			{
				$vars['view'] = 'category';
				$vars['id'] = $cat_id;
			}

			return $vars;
		}

		// We get the category id from the menu item and search from there
		if(isset($item->query['id'])){
			$id = $item->query['id'];
		}else{
			$id = 0;
		}

		$category = JCategories::getInstance('DigiCom')->get($id);

		if (!$category)
		{
			JError::raiseError(404, JText::_('COM_DIGICOM_ERROR_PARENT_CATEGORY_NOT_FOUND'));

			return $vars;
		}

		$categories = $category->getChildren();
		$vars['catid'] = $id;
		$vars['id'] = $id;
		$found = 0;

		foreach ($segments as $segment)
		{
			$segment = str_replace(':', '-', $segment);

			foreach ($categories as $category)
			{
				if ($category->alias == $segment)
				{
					$vars['id'] = $category->id;
					$vars['catid'] = $category->id;
					$vars['view'] = 'category';
					$categories = $category->getChildren();
					$found = 1;
					break;
				}
			}

			if ($found == 0)
			{
				if ($advanced)
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select($db->quoteName('id'))
						->from('#__digicom_products')
						->where($db->quoteName('catid') . ' = ' . (int) $vars['catid'])
						->where($db->quoteName('alias') . ' = ' . $db->quote($segment));
					$db->setQuery($query);
					$cid = $db->loadResult();
				}
				else
				{
					$cid = $segment;
				}

				$vars['id'] = $cid;

				//				if ($item->query['view'] == 'archive' && $count != 1)
				//				{
				//					$vars['year'] = $count >= 2 ? $segments[$count - 2] : null;
				//					$vars['month'] = $segments[$count - 1];
				//					$vars['view'] = 'archive';
				//				}
				//				else
				//				{
					$vars['view'] = 'product';
				//}
			}

			$found = 0;
		}

		return $vars;
	}
}

/**
 * DigiCom router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function digicomBuildRoute(&$query)
{
	$router = new DigiComRouter;

	return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function digicomParseRoute($segments)
{
	$router = new DigiComRouter;

	

	return $router->parse($segments);
}
