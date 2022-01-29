<?php
/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-09-27 07:54:44
 * @@Function:
 */

namespace Magiccart\Magicproduct\Block\Widget;

class Product extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    protected $_storeManager;
	protected $_magicproduct;
    protected $_types;
	protected $_tabs = array();
    protected $_typeId = '1';
    protected $_options = array('limit', 'speed', 'timer', 'cart', 'compare', 'wishlist', 'review'); //'widthImages', 'heightImages'
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magiccart\Magicproduct\Model\Magicproduct $magicproduct,
		\Magiccart\Magicproduct\Model\System\Config\Types $types,
        array $data = []
    ) {
        $this->_magicproduct = $magicproduct;
		$this->_types = $types;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
		$identifier = $this->getIdentifier();
		$item = $this->_magicproduct->getCollection()->addFieldToSelect('config')
                        ->addFieldToFilter('identifier', $identifier)->addFieldToFilter('type_id', $this->_typeId)->getFirstItem();
		$config = $item->getConfig();
		$data = @unserialize($config);
        if(!$data){
            // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // $messageManager = $objectManager->create('Magento\Framework\View\Element\Messages');
            // $messageManager->setHasError(true)->addError( __('This is your error message.') );
            echo '<div class="message-error error message">Identifier "'. $identifier . '" not exist.</div> ';          
            return;
        }
		if($data['slide']){
            $breakpoints = $this->getResponsiveBreakpoints();
            $total = count($breakpoints);
            $responsive = '[';
            foreach ($breakpoints as $size => $screen) {
            	$responsive .= '{"breakpoint": "'.$size.'", "settings": {"slidesToShow": "'.$data[$screen].'"}}';
                if($total-- > 1) $responsive .= ', ';
            }
            $responsive .= ']';
            $data['responsive'] = $responsive;
            $data['slides-To-Show'] = $data['visible'];
            $data['swipe-To-Slide'] = 'true';
            $data['vertical-Swiping'] = $data['vertical'];
		}
        //$data['lazy-Load'] = 'progressive';
        $this->addData($data);
        parent::_construct();
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

    public function getTabActivated()
    {
        $activated = $this->getActivated(); // get form Widget
        $tabs = $this->getTabs();
        $types = array_keys($tabs);
        if(!in_array($activated, $types)){
            $activated = isset($types[0]) ? $types[0] : 0;            
        }
        return $activated;
    }

    public function getContent($template)
    {
        $content = '';    
        $tabs = ($this->getAjax()) ? $tabs = array($this->getTabActivated() => 'Activated') : $this->getTabs();
        foreach ($tabs as $type => $name) {
            $content .= $this->getLayout()->createBlock('Magiccart\Magicproduct\Block\Product\GridProduct') // , "magicproduct.product.$type"
            ->setActivated($type) //or ->setData('activated', $this->getTabActivated())
            ->setCfg($this->getData())
            ->setTemplate($template)
            ->toHtml();
        }
        return $content;
    }

    public function getAjaxCfg()
    {
    	if(!$this->getAjax()) return 0;
        $ajax = array();
        foreach ($this->_options as $option) {
            $ajax[$option] = $this->getData($option);
        }
        return json_encode($ajax);
    }

    public function getPrcents()
    {
        return array(1 => '100%', 2 => '50%', 3 => '33.333333333%', 4 => '25%', 5 => '20%', 6 => '16.666666666%', 7 => '14.285714285%', 8 => '12.5%');
    }

    public function getResponsiveBreakpoints()
    {
        return array(1201=>'visible', 1200=>'desktop', 992=>'notebook', 769=>'tablet', 641=>'landscape', 481=>'portrait', 361=>'mobile', 1=>'mobile');
    }

    public function getSlideOptions()
    {
        return array('autoplay', 'arrows', 'autoplay-Speed', 'dots', 'infinite', 'padding', 'vertical', 'vertical-Swiping', 'responsive', 'rows', 'slides-To-Show', 'swipe-To-Slide');
    }

    public function getFrontendCfg()
    { 
        if($this->getSlide()) return $this->getSlideOptions();

        $this->addData(array('responsive' =>json_encode($this->getGridOptions())));
        return array('padding', 'responsive');

        // return $this->getGridStyle();

    }

    public function getGridOptions()
    {
        $options = array();
        $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
        foreach ($breakpoints as $size => $screen) {
            $options[]= array($size-1 => $this->getData($screen));
        }
        return $options;

        // $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
        // $total= count($breakpoints);
        // $i = $tmp = 1;
        // $options = array();
        // foreach ($breakpoints as $key => $value) {
        //     $tmpKey = ( $i == 1 || $i == $total ) ? $value : current($breakpoints);
        //     if($i >1){
        //         $options[] = ['col' => $this->getData($value), 'min' => $tmp, 'max' => ($key-1)];
        //         next($breakpoints);
        //     }
        //     if($i == $total) $options[] = ['col' => $this->getData($value), 'min' => $key, 'max' => 3600,];
        //     $tmp = $key;
        //     $i++;
        // }
        // return $options;

    }

    function getGridStyle($selector=' .products-grid .product-item')
    {
        $styles = '';
        $listCfg = $this->getData();
        $padding = $listCfg['padding'];
        $prcents = $this->getPrcents();
        $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
        $total= count($breakpoints);
        $i = $tmp = 1;
        foreach ($breakpoints as $key => $value) {
            $tmpKey = ( $i == 1 || $i == $total ) ? $value : current($breakpoints);;
            if($i >1){
                $styles .= ' @media (min-width: '. $tmp .'px) and (max-width: ' . ($key-1) . 'px) {' .$selector. '{padding: 0 '.$padding.'px; width: '.$prcents[$listCfg[$value]] .'} ' .$selector. ':nth-child(' .$listCfg[$value]. 'n+1){clear: left;}}';
                next($breakpoints);
            }
            if( $i == $total) $styles .= ' @media (min-width: ' . $key . 'px) {' .$selector. '{padding: 0 '.$padding.'px; width: '.$prcents[$listCfg[$value]] .'} ' .$selector. ':nth-child(' .$listCfg[$value]. 'n+1){clear: left;}}';
            $tmp = $key;
            $i++;
        }
        return '<style type="text/css">' .$styles. '</style>';       
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

}
