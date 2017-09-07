<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\BarcodeSuccess\Block\Barcode\Container;

use Magestore\BarcodeSuccess\Model\Source\MeasurementUnit;
use Magestore\BarcodeSuccess\Model\Source\TemplateType;

class Template extends \Magestore\BarcodeSuccess\Block\Barcode\Container
{

    public function getBarcodes(){
        $barcodes = [];
        $datas = $this->getData('barcodes');
        if($datas){
            $template = $this->getTemplateData();
            foreach ($datas as $data){
                if(empty($data['qty'])){
                    $data['qty'] = $template['label_per_row'];
                }
                for($i = 1; $i <= $data['qty']; $i++){
                    $barcodes[] = $this->getBarcodeSource($data);
                }
            }
        }
        return $barcodes;
    }

    /**
     * @return string
     */
    public function getBarcodeSource($data){
        $source = "";
        if($data){
            $template = $this->getTemplateData();
            $type = $template['symbology'];
            $barcodeOptions = array('text' => $data['barcode'],
                'fontSize' => $template['font_size']
            );
            $rendererOptions = array(
                //'width' => '198',
                'height' => '0',
                'imageType' => 'png'
            );
            $source = \Zend_Barcode::factory(
                $type, 'image', $barcodeOptions, $rendererOptions
            );

            if(isset($template['product_attribute_show_on_barcode'])){
                $attributeDatas = $this->getAttributeSoucre($data,$template['product_attribute_show_on_barcode']);
            }else{
                $attributeDatas = array();
            }
        }
        $result['attribute_data'] = $attributeDatas;
        $result['barcode_source'] = $source;
        return $result;
    }

    public function getAttributeSoucre($data,$attributes){
        if(!isset($data['product_id'])){
            $product_id = 1;
        }else{
            $product_id = $data['product_id'];
        }
        $attributeArray = array();
        if($product_id && $attributes && $attributes != ''){
            if(is_array($attributes)){
                $array = $attributes;
            }else{
                $array = explode(',' ,$attributes);
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Catalog\Model\Product $product */
            $prod = $objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
            foreach($array as $key){
                if( $key && ($text = ($prod->getData($key) ? $prod->getData($key) : $prod->getData($key))))
                {
                    if( ($key ==='sku') ||($key ==='name') ){
                        $attributeArray[] =  (is_numeric($text) ? (int)$text : $text);
                    }elseif(($key ==='price')){
                        $price = $text; //Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->toCurrency($text);
                        $attributeArray[] = '[' . $key . '] ' . $price;
                    } else {
                        $attributeArray[] = '[' . $key . '] ' . (is_numeric($text) ? (int)$text : $text);
                    }
                }
            }
        }
        return $attributeArray;

    }

    /**
     * @return string
     */
    public function getTemplateData(){
        $data = [];
        if($this->getData('template_data')){
            $data = $this->getData('template_data');
        }
        if(empty($data['font_size'])){
            $data['font_size'] = '24';
        }
        if(empty($data['label_per_row'])){
            $data['label_per_row'] = '1';
        }
        if(empty($data['measurement_unit'])){
            $data['measurement_unit'] = MeasurementUnit::MM;
        }
        if(empty($data['paper_height'])){
            $data['paper_height'] = '30';
        }
        if(empty($data['paper_width'])){
            $data['paper_width'] = '100';
        }
        if(empty($data['label_height'])){
            $data['label_height'] = '30';
        }
        if(empty($data['label_width'])){
            $data['label_width'] = '100';
        }
        if(empty($data['left_margin'])){
            $data['left_margin'] = '0';
        }
        if(empty($data['right_margin'])){
            $data['right_margin'] = '0';
        }
        if(empty($data['bottom_margin'])){
            $data['bottom_margin'] = '0';
        }
        if(empty($data['top_margin'])){
            $data['top_margin'] = '0';
        }
        return $data;
    }

    /**
     * @return bool
     */
    public function isJewelry(){
        $template = $this->getTemplateData();
        return ($template['type'] == TemplateType::JEWELRY)?true:false;
    }
}
