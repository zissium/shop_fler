<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-02-14 20:26:27
 * @@Modify Date: 2017-01-22 16:14:15
 * @@Function:
 */

namespace Magiccart\Alothemes\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_labels = null;
    protected $_timer  = null;
    protected $_themeCfg = array();

    public function getConfig($cfg='')
    {
        if($cfg) return $this->scopeConfig->getValue( $cfg, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        return $this->scopeConfig;
    }

    public function getThemeCfg($cfg='')
    {
        if(!$this->_themeCfg) $this->_themeCfg = $this->getConfig('alothemes');
        if(!$cfg) return $this->_themeCfg;
        elseif(isset($this->_themeCfg[$cfg])) return $this->_themeCfg[$cfg];
    }

    public function getImageHover($_product)
    {
        return  $_product->load('media_gallery')->getMediaGalleryImages()->getItemByColumnValue('position','2')->getFile(); //->getItemByColumnValue('label','Imagehover')
    }

    public function getTimer($_product)
    {
        if($this->_timer==null) $this->_timer = $this->getThemeCfg('timer');
        if(!$this->_timer['enabled']) return;
        $toDate = $_product->getSpecialToDate();
        if(!$toDate) return;
        if($_product->getPrice() < $_product->getSpecialPrice()) return;
        if($_product->getSpecialPrice() == 0 || $_product->getSpecialPrice() == "") return;
        $timer = strtotime($toDate) - strtotime("now");
        return '<div class="alo-count-down"><div class="countdown" data-timer="' .$timer. '"></div></div>';

        $now = new \DateTime();
        $ends = new \DateTime($toDate);
        $left = $now->diff($ends);
        return '<div class="alo-count-down"><span class="countdown" data-d="' .$left->format('%a'). '" data-h="' .$left->format('%h'). '" data-i="' .$left->format('%h'). '" data-s="' .$left->format('%s'). '"></span></div>';
    }

    public function getLabels($product)
    {
        if($this->_labels==null) $this->_labels = $this->getThemeCfg('labels');
        $html  = '';
        $newText = isset($this->_labels['newText']) ? $this->_labels['newText'] : ''; // get in Cfg;
        if($newText && $this->isNew($product)) $html .= '<span class="sticker top-left"><span class="labelnew">' . __($newText) . '</span></span>';
        $percent = isset($this->_labels['salePercent']) ? $this->_labels['salePercent'] : false; // get in Cfg;
        if($percent){
            $price = $product->getPrice();
            $finalPrice = $product->getFinalPrice();
            $saleLabel = (int)$price ? floor(($finalPrice/$price)*100 - 100).'%' : '';
        }else {
            $saleLabel = isset($this->_labels['saleText']) ? $this->_labels['saleText'] : '';
        }
        if($saleLabel && $this->isOnSale($product)) $html .= '<span class="sticker top-right"><span class="labelsale">' . __($saleLabel) . '</span></span>';
        
        return $html;
    }

    protected function isNew($product)
    {
        return $this->_nowIsBetween($product->getData('news_from_date'), $product->getData('news_to_date'));
    }

    protected function isOnSale($product)
    {
        $specialPrice = number_format($product->getFinalPrice(), 2);
        $regularPrice = number_format($product->getPrice(), 2);

        if ($specialPrice != $regularPrice) return $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date'));
        else return false;
    }
    
    protected function _nowIsBetween($fromDate, $toDate)
    {
        if ($fromDate){
            $fromDate = strtotime($fromDate);
            $toDate = strtotime($toDate);
            $now = strtotime(date("Y-m-d H:i:s"));
            
            if ($toDate){
                if ($fromDate <= $now && $now <= $toDate) return true;
            }else {
                if ($fromDate <= $now) return true;
            }
        }
        return false;
    }

    public function getPrcents()
    {
        return array(1 => '100%', 2 => '50%', 3 => '33.333333333%', 4 => '25%', 5 => '20%', 6 => '16.666666666%', 7 => '14.285714285%', 8 => '12.5%');
    }

    public function getResponsiveBreakpoints()
    {
        return array(1201=>'visible', 1200=>'desktop', 992=>'notebook', 769=>'tablet', 641=>'landscape', 481=>'portrait', 361=>'mobile', 1=>'mobile');
    }

    public function getGridStyle($selector=' .products-grid .product-item')
    {
        $styles = $selector .'{ float: left;}';
        $listCfg  = $this->getConfig('alothemes/grid');
        $padding = $listCfg['padding'];
        $prcents = $this->getPrcents();
        $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
        $total = count($breakpoints);
        $i = $tmp = 1;
        foreach ($breakpoints as $key => $value) {
            $tmpKey = ( $i == 1 || $i == $total) ? $value : current($breakpoints);
            if($i >1){
                $styles .= ' @media (min-width: '. $tmp .'px) and (max-width: ' . ($key-1) . 'px) {' .$selector. '{padding: 0 '.$padding.'px; width: '.$prcents[$listCfg[$value]] .'} ' .$selector. ':nth-child(' .$listCfg[$value]. 'n+1){clear: left;}}';
                next($breakpoints);
            }
            if( $i == $total ) $styles .= ' @media (min-width: ' . $key . 'px) {' .$selector. '{padding: 0 '.$padding.'px; width: '.$prcents[$listCfg[$value]] .'} ' .$selector. ':nth-child(' .$listCfg[$value]. 'n+1){clear: left;}}';
            $tmp = $key;
            $i++;
        }
        return  '<style type="text/css">' .$styles. '</style>';
    }


    public function getConfgRUC($type) // with Type = 'related' || 'upsell' || 'crosssell'
    {
        $data = $this->getConfig('alothemes/' .$type);
        $breakpoints = $this->getResponsiveBreakpoints();
        $total = count($breakpoints);
        if($data['slide']){
            $data['vertical-Swiping'] = $data['vertical'];
            $responsive = '[';
            foreach ($breakpoints as $size => $opt) {
                $responsive .= '{"breakpoint": "'.$size.'", "settings": {"slidesToShow": "'.$data[$opt].'"}}';
                $total--;
                if($total) $responsive .= ', ';
            }
            $responsive .= ']';
            $data['slides-To-Show'] = $data['visible'];
            $data['swipe-To-Slide'] = 'true';
            $data['responsive'] = $responsive;
            $Rm = array('slide', 'visible', 'desktop', 'notebook', 'tablet', 'landscape', 'portrait', 'mobile'); // require with slick
            foreach ($Rm as $vl) { unset($data[$vl]); }

            return $data;

        } else {
            $options = array();
            $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
            foreach ($breakpoints as $size => $screen) {
                $options[]= array($size => $data[$screen]);
            }
            return array('padding' => $data['padding'], 'responsive' =>json_encode($options));
            
            // $prcents = $this->getPrcents();
            // $padding = $data['padding'];
            // $selector = '.' . $type .' .products-grid .product-item';
            // $styles = $selector .'{ float: left;}';
            // $i = $tmp= 1;
            // foreach ($breakpoints as $key => $value) {
            //     $tmpKey = ( $i == 1 || $i == $total ) ? $value : current($breakpoints);
            //     if($i >1){
            //         $styles .= ' @media (min-width: '. $tmp .'px) and (max-width: ' . ($key-1) . 'px) {' .$selector. '{padding: 0 '.$padding.'px; width: '.$prcents[$data[$value]] .'} ' .$selector. ':nth-child(' .$data[$value]. 'n+1){clear: left;}}';
            //         next($breakpoints);
            //     }
            //     if( $i == $total ) $styles .= ' @media (min-width: ' . $key . 'px) {' .$selector. '{padding: 0 '.$padding.'px; width: '.$prcents[$data[$value]] .'} ' .$selector. ':nth-child(' .$data[$value]. 'n+1){clear: left;}}';
            //     $tmp = $key;
            //     $i++;
            // }

            // return '<style type="text/css">' .$styles. '</style>';
        }
    }
}
