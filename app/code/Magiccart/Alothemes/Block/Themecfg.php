<?php
/**
 * Magiccart 
 * @category  Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license   http://www.magiccart.net/license-agreement.css
 * @Author: Magiccart<team.magiccart@gmail.com>
 * @@Create Date: 2016-02-28 10:10:00
 * @@Modify Date: 2018-07-06 18:21:47
 * @@Function:
 */
namespace Magiccart\Alothemes\Block;
use Magento\Framework\App\Filesystem\DirectoryList;
class Themecfg extends \Magento\Framework\View\Element\Template
{
    public $_themeCfg;
    public $_time;
    public $_rtl;
    public $_scopeConfig;
    public $assetRepository;
    public $filesystem;
    public $cssFile = '_cache/merged/stores/%s/alothemes_custom.css';
    public $storeManager;
    protected $_dir;
    protected $storeId;
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $time,
        \Magiccart\Alothemes\Helper\Data $_helper,
        array $data = []
	) {
        parent::__construct($context, $data);
        $this->_time  		= $time;
		$this->_themeCfg 	= $_helper->getThemeCfg();
		$this->_scopeConfig = $context->getScopeConfig();
        $this->filesystem   = $context->getFilesystem();
        $this->assetRepository = $context->getAssetRepository();
		$this->_rtl = (isset($this->_themeCfg['rtl']['enabled']) && $this->_themeCfg['rtl']['enabled']) ? 'rtl' : '';
		if($this->_rtl) $context->getPageConfig()->addBodyClass($this->_rtl);
        $mergeCss = $this->_scopeConfig->getValue( 'dev/css/merge_css_files', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        if($mergeCss){
            $this->storeManager = $context->getStoreManager();
            $this->storeId      = $this->storeManager->getStore()->getId();
            $this->cssFile      = sprintf($this->cssFile, $this->storeId);

            $this->createAsset();
        } else {
            $this->cssFile = '';
        }

	}

    public function createAsset()
    {
        $this->_dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $cssFilePath = $this->_dir->getAbsolutePath($this->cssFile);
        if(!$this->_dir->isExist($cssFilePath)){
            try {
                $css = $this->generalCss();
                $this->_dir->writeFile($this->cssFile, $css);
            } catch (FileSystemException $e) {
                $this->cssFile = '';
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
          
        }

    }

    public function generalCss()
    {

        $cfg = $this->_themeCfg;
        $css ='';
        $font   = $cfg['font'];
        /* CssGenerator */
        $css  .= 'body{ font-size: '.$font['size'].'; font-family: '.$font['google'].';}';

        $design = $this->_scopeConfig->getValue( 'alodesign', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        foreach ($design as $group => $options) 
        {
            foreach ($options as $property => $values) {
                $tmp = json_decode($values, true);
                if(json_last_error() == JSON_ERROR_NONE){
                    $values = $tmp;
                } else {
                    $values = @unserialize($values);
                }
                if(is_array($values) || is_object($values)){
                    foreach ($values as $value) {
                        if(!$value) continue;
                        $css .= htmlspecialchars_decode($value['selector']) .'{';
                            $css .= $value['color']        ? 'color:' .$value['color']. ';'                    : '';
                            $css .= $value['background']   ? ' background-color:' .$value['background']. ';'   : '';
                            $css .= $value['border']       ? ' border-color:' .$value['border']. ';'           : '';
                        $css .= '}';
                    }               
                }

            }
        }

        if(isset($design['extra_css']['color'])) $css .= $design['extra_css']['color'];

        return $css;
    }

    public function getExtraCssUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC) . $this->cssFile;
    }
}
