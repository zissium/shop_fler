<?php
/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-05-24 22:53:12
 * @@Function:
 */

namespace Magiccart\Magicproduct\Block\Widget;

class Category extends Product
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

	protected $_typeId = '2';
    protected $_options = array('limit', 'speed', 'timer', 'cart', 'compare', 'wishlist', 'review', 'types'); //'widthImages', 'heightImages'

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

    public function getTabs()
    {
        if(!$this->_tabs){
            $tabs = array();
            $categoryIds = $this->getCategoryIds();
            $types =  $this->_categoryInstance->getCollection()
                            // ->setStoreId()
                            ->addAttributeToFilter('entity_id', array('in' => $categoryIds))
                            ->addAttributeToSelect('name');
            foreach ($types as $type) {
                $tabs[$type->getEntityId()] = $type->getName();
            }
            if(!count($tabs)){
                $types =  $this->_catalogCategory->getStoreCategories();
                $maxTab = 5;
                $i = 1;
                foreach ($types as $type) {
                    $tabs[$type->getEntityId()] = $type->getName();
                    if($i == $maxTab) break;
                    $i++;
                }
            }
            $this->_tabs = $tabs;
        }
        return $this->_tabs;
    }

    public function getContent($template)
    {
        $content = '';   
        $tabs = ($this->getAjax()) ? $tabs = array($this->getTabActivated() => 'Activated') : $this->getTabs();
    	foreach ($tabs as $type => $name) {
    		$content .= $this->getLayout()->createBlock('Magiccart\Magicproduct\Block\Category\GridProduct') //, "magicproduct.category.$type"
           	->setActivated($type) //or ->setData('activated', $this->getTabActivated())
           	->setCfg($this->getData())
           	->setTemplate($template)
           	->toHtml();
        }
    	return $content;
    }

}
