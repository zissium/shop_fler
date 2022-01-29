<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-02-14 20:26:27
 * @@Modify Date: 2016-03-14 15:10:46
 * @@Function:
 */

namespace Magiccart\Alothemes\Helper;

class Search extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SECTIONS      = 'alothemes';      // module name
    const GROUPS_SEARCH= 'categorysearch';    // setup general

    protected $config = array();

    public function getConfig($cfg)
    {
        if(!$this->config) $this->config = Mage::getStoreConfig(self::SECTIONS.'/'.self::GROUPS_SEARCH);
        if(isset($this->config[$cfg])) return $this->config[$cfg];
    }

    public function selectCategoryOnCategoryPages() {
        return $this->getConfig('select_category_on_category_pages');
    }

    public function getCategoryParamName() {
        return Mage::getModel('catalog/layer_filter_category')->getRequestVar();
    }

    public function getMaximumCategoryLevel() {
        return $this->showSubCategories() ? 3 : 2;
    }

    public function isCategoryPage() {
        return Mage::app()->getFrontController()->getAction() instanceof Mage_Catalog_CategoryController;
    }

    public function isSearchResultsPage() {
        return Mage::app()->getFrontController()->getAction() instanceof Mage_CatalogSearch_ResultController;
    }

}
