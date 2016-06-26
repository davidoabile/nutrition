<?php

class Infortis_Ultimo_Helper_Template_Page_Html_Header extends Mage_Core_Helper_Abstract
{
	/**
	 * Menu module name
	 *
	 * @var string
	 */
	protected $_menuModuleName = 'Infortis_UltraMegamenu';
	protected $_menuModuleNameShort = 'ultramegamenu';

	/**
	 * Get mobile menu threshold from the menu module.
	 * If module not enabled, return NULL.
	 *
	 * @return string
	 */
	public function getMobileMenuThreshold()
	{
		if(Mage::helper('core')->isModuleEnabled($this->_menuModuleName))
		{
			return Mage::helper($this->_menuModuleNameShort)->getMobileMenuThreshold();
		}
		return NULL;
	}

	/**
	 * Create grid classes for header sections
	 *
	 * @return array
	 */
	public function getGridClasses()
	{
		$theme = Mage::helper('ultimo');

		//Width (in grid units) of product page sections
		$primLeftColUnits		= $theme->getCfg('header/left_column');
		$primCentralColUnits	= $theme->getCfg('header/central_column');
		$primRightColUnits		= $theme->getCfg('header/right_column');

		//Grid classes
		$classPrefix = 'grid12-';

		if (!empty($primLeftColUnits) && trim($primLeftColUnits) !== '')
		{
			$grid['primLeftCol'] 		= $classPrefix . $primLeftColUnits;
		}

		if (!empty($primCentralColUnits) && trim($primCentralColUnits) !== '')
		{
			$grid['primCentralCol']		= $classPrefix . $primCentralColUnits;
		}

		if (!empty($primRightColUnits) && trim($primRightColUnits) !== '')
		{
			$grid['primRightCol']		= $classPrefix . $primRightColUnits;
		}

		return $grid;
	}

}
