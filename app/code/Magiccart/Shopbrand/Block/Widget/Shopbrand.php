<?php
/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-05-13 16:24:14
 * @@Function:
 */

namespace Magiccart\Shopbrand\Block\Widget;

class Shopbrand extends Brand
{

    protected $_types;
    protected $_tabs = array();

    public function getTabs()
    {
        return $this->getBrands();
    }

    public function getTabActivated()
    {
        if($this->hasData('activated')) return $this->getData('activated');
        $activated = $this->getTabs()->getFirstItem();
        $brandId = $activated->getBrandId();
        if(!$brandId) return 0;
        $this->setData('activated', $brandId);
        return $brandId;
    }

    public function getAjaxCfg()
    {
        if(!$this->getAjax()) return 0;
        $options = array('limit', 'speed', 'timer', 'cart', 'compare', 'wishlist', 'review'); //'widthImages', 'heightImages'
        $ajax = array();
        foreach ($options as $option) {
            $ajax[$option] = $this->getData($option);
        }
        return json_encode($ajax);
    }

    public function getContent($template)
    {
        $content = '';    
        $tabs = ($this->getAjax()) ? $tabs = array($this->getTabActivated() => 'Activated') : $this->getTabs();
        foreach ($tabs as $type => $name) {
            $content .= $this->getLayout()->createBlock('Magiccart\Shopbrand\Block\Product\GridProduct') // , "magicproduct.product.$type"
            ->setActivated($type) //or ->setData('activated', $this->getTabActivated())
            ->setCfg($this->getData())
            ->setTemplate($template)
            ->toHtml();
        }
        return $content;
    }

}
