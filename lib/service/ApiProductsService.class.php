<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiProductsService
 *
 * @author Sophie MICHEL <sophie.michel@libre-informatique.fr>
 */
class ApiProductsService  extends ApiEntityService {
    
    protected $translationService;
    protected $oauth;
    protected static $FIELD_MAPPING = [
        'id'                    => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'category'              => ['type' => 'single', 'value' => 'Category.name', 'updatable' => false],
        'translations'          => ['type' => 'collection', 'value' => 'Translation', 'updatable' => false],
        'declinations.id'       => ['type' => 'collection.single', 'value' => 'Declinations.id', 'updatable' => false],
        'declinations.code'     => ['type' => 'collection.single', 'value' => 'Declinations.code', 'updatable' => false],
        'declinations.weight'   => ['type' => 'collection.single', 'value' => 'Declinations.weight', 'updatable' => false],
        'declinations.availableUnits'   => ['type' => 'collection.single', 'value' => 'Declinations.availableUnits', 'updatable' => false],
        'prices.id'             => ['type' => 'collection.single', 'value' => 'Prices.id', 'updatable' => false],        
        'prices.translations'   => ['type' => 'collection.single', 'value' => 'Prices.Translation', 'updatable' => false],        
        'prices.value'          => ['type' => 'collection.single', 'value' => 'Prices.value', 'updatable' => false], 
                
                
    ];
      
    
    public function buildInitialQuery()
    {
           return parent::buildInitialQuery();
    }

    public function setTranslationService(ApiTranslationService $i18n) {
        $this->translationService = $i18n;
        return $this;
    }

    public function setOAuthService(ApiOAuthService $service) {
        $this->oauth = $service;
    }

    public function getOAuthService() {
        return $this->oauth;
    }
    
    public function setProductService(ProductService $service)
    {
        $this->productsService = $service;
    }
    public function getMaxShownAvailableUnits()
    {
        return 10;
    }
    protected function postFormatEntity(array $entity, Doctrine_Record $product) {
        // translations
        $this->translationService
                ->reformat($entity['translations'])
        ;

        // imageURL
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
        $entity['imageUrl'] = url_for('@os_api_picture?id=' . $entity['id']);
        
        // currency
        $currency = sfConfig::get('project_internals_currency', ['iso' => 978, 'symbol' => '€']);
        $entity['currencyCode'] = $currency['iso'];

        
        return $entity;
     }

    public function getBaseEntityName() 
    {
        return 'Product';
    }

}
