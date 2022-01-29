<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-11 23:15:05
 * @@Modify Date: 2016-03-14 15:26:36
 * @@Function:
 */

namespace Magiccart\Alothemes\Model\System\Config;

class Size implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return array(
			array('value' => '12px',	'label' => __('12 px')),
			array('value' => '13px',	'label' => __('13 px')),
            array('value' => '14px',	'label' => __('14 px')),
            array('value' => '16px',	'label' => __('16 px'))
        );
    }
}

