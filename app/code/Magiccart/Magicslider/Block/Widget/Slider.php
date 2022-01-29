<?php
/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2017-01-05 10:40:51
 * @@Modify Date: 2018-07-24 18:09:48
 * @@Function:
 */

namespace Magiccart\Magicslider\Block\Widget;
use Magento\Framework\App\Filesystem\DirectoryList;

class Slider extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    public $_sysCfg;

    protected $_imageFactory;
    protected $_filesystem;
    protected $_directory;

    protected $_magicslider;
    protected $_images = array();

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magiccart\Magicslider\Model\Magicslider $magicslider,
        array $data = []
    ) {

        $this->_imageFactory = $imageFactory;
        $this->_filesystem = $context->getFilesystem();
        $this->_directory = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        $this->_magicslider = $magicslider;

        $this->_sysCfg= (object) $context->getScopeConfig()->getValue(
            'magicslider',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $identifier = $this->getIdentifier();
        // $store = $this->_storeManager->getStore()->getStoreId();
        $item = $this->_magicslider->getCollection()->addFieldToSelect('config')
                        // ->addFieldToFilter('stores',array( array('finset' => 0), array('finset' => $store)))
                        ->addFieldToFilter('status', 1)
                        ->addFieldToFilter('identifier', $identifier)->getFirstItem();
                        
        $data = json_decode($item->getConfig(), true);
        if(!$data){
            echo '<div class="message-error error message">Identifier "'. $identifier . '" not exist.</div> ';          
            return;
        }

        $breakpoints = $this->getResponsiveBreakpoints();
        $total = count($breakpoints);
        $responsive = '[';
        foreach ($breakpoints as $size => $screen) {
            if(isset($data[$screen])){
                $responsive .= '{"breakpoint": "'.$size.'", "settings": {"slidesToShow": "'.$data[$screen].'"}}';
            }
            if($total-- > 1) $responsive .= ', ';
        }
        $responsive .= ']';
        $data['responsive'] = $responsive;
        $data['slides-To-Show'] = $data['visible'];
        $data['swipe-To-Slide'] = 'true';
        $data['vertical-Swiping'] = $data['vertical'];
        $data['slide'] = 1;
        //$data['lazy-Load'] = 'progressive';
        $this->addData($data);
        parent::_construct();
    }

    public function getSlider()
    {
        if(!$this->_images){
            $gallery = $this->getData('media_gallery');
            $this->_images = $gallery['images'];
        }
        return $this->_images;
    }

    public function getImage($file)
    {
        // $width  =200;
        // $height = 200;
        // $directory = $width . 'x' . $height;
        // $image = $object->getImage();
        // $absPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath().$image;
        // $imageResized = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($directory).$image;
        // $imageResize = $this->_imageFactory->create();
        // $imageResize->open($absPath);
        // $imageResize->constrainOnly(TRUE);
        // $imageResize->keepTransparency(TRUE);
        // $imageResize->keepFrame(FALSE);
        // $imageResize->keepAspectRatio(true);
        // $imageResize->resize($width, $height);
        // $dest = $imageResized ;
        // $imageResize->save($dest);
        // $resizedURL= $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$directory.$image;
        
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .'magiccart/magicslider'. $file;
        return $resizedURL;
    }

    public function getVideo($data){
        $url = str_replace('vimeo.com', 'player.vimeo.com/video', $data['video_url']) .'?byline=0&amp;portrait=0&amp;api=1';
        $video = array(
            'url' => $url,
            'width' => '100%',
            'height' => '100%'
        );
        $file = 'magiccart/magicslider'. $data['file'];
        $absPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath().$file;
        $image = $this->_imageFactory->create();
        $image->open($absPath);
        $video['width'] = $image->getOriginalWidth();
        $video['height'] = $image->getOriginalHeight();

        return $video;
    }

    public function getResponsiveBreakpoints()
    {
        return array(1921=>'visible', 1920=>'desktop', 1200=>'laptop', 992=>'notebook', 768=>'tablet', 576=>'landscape', 480=>'portrait', 361=>'mobile', 1=>'mobile');
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

    }

    public function getGridOptions()
    {
        $options = array();
        $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
        foreach ($breakpoints as $size => $screen) {
            $options[]= array($size-1 => $this->getData($screen));
        }
        return $options;
    }

}
