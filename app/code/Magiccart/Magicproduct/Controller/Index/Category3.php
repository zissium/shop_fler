<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-03-29 23:43:47
 * @@Function:
 */

namespace Magiccart\Magicproduct\Controller\Index;

class Category3 extends \Magiccart\Magicproduct\Controller\Index
{
    /**
     * Default customer account page.
     */
    public function execute()
    {
    	if ($this->getRequest()->isAjax()) {
	        $this->_view->loadLayout();
	        $this->_view->renderLayout();
	        $info = $this->getRequest()->getParam('info');
	        $type = $this->getRequest()->getParam('type');
	        $tmp = $info['timer'] ? 'category/grid-multi.phtml':'category/grid-multi.phtml';
	        $products = $this->_view->getLayout()->createBlock('Magiccart\Magicproduct\Block\Category\GridProduct')
					            ->setCfg($info)
					           	->setActivated($type)
					           	->setTemplate($tmp)
					           	->toHtml();
	        $this->getResponse()->setBody( $products );
	    }else {
	        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
	        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
	        return $resultRedirect;
	    }
    }

}
