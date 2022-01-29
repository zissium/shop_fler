<?php
/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-06-29 09:50:18
 * @@Function:
 */

namespace Magiccart\Magicproduct\Block\Widget;

class Catalog extends Product
{

    /**
     * @var Category
     */
    protected $_categoryInstance;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

	protected $_typeId = '3';
    protected $_options = array('limit', 'speed', 'timer', 'cart', 'compare', 'wishlist', 'review', 'category_id'); //'widthImages', 'heightImages'

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magiccart\Magicproduct\Model\Magicproduct $magicproduct,
		\Magiccart\Magicproduct\Model\System\Config\Types $types,
        array $data = []
    ) {

        $this->_catalogCategory = $catalogCategory;
        $this->_categoryInstance = $categoryFactory->create();

        parent::__construct($context, $magicproduct, $types, $data);
    }

    public function getCatName()
    {
        $cat = $this->_categoryInstance->load($this->getData('category_id'));
        if($cat) return $cat->getName();
    }

    public function getTabs()
    {
        if(!$this->_tabs){
            $tabs = array();
            $cfg = $this->getTypes();
            $types = $this->_types->toOptionArray();
            foreach ($types as $type) {
                if(in_array($type['value'], $cfg)) $tabs[$type['value']] = $type['label'];
            }
            $this->_tabs = $tabs;
        }
        return $this->_tabs;
    }

    public function getRelatedTabs()
    {

        $categoryIds = $this->getCategoryIds();
        $categories =  $this->_categoryInstance->getCollection()
                        // ->setStoreId()
                        ->addAttributeToFilter('entity_id', array('in' => $categoryIds))
                        ->addAttributeToSelect('name');
        return $categories;
    }

    public function getContent($template)
    {
        $content = '';   
        $tabs = ($this->getAjax()) ? $tabs = array($this->getTabActivated() => 'Activated') : $this->getTabs();
    	foreach ($tabs as $type => $name) {
    		$content .= $this->getLayout()->createBlock('Magiccart\Magicproduct\Block\Catalog\GridProduct') //, "magicproduct.category.$type"
           	->setActivated($type) //or ->setData('activated', $this->getTabActivated())
           	->setCfg($this->getData())
           	->setTemplate($template)
           	->toHtml();
        }
    	return $content;
    }

}
