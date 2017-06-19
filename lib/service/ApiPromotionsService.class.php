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
class ApiPromotionsService  extends ApiEntityService {
    
    protected $translationService;
    protected $oauth;
    protected static $FIELD_MAPPING = [
        'id'                    => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'type'                  => ['type' => 'single', 'value' => 'Type.name', 'updatable' => false],
        'translations'          => ['type' => 'collection', 'value' => 'Translation', 'updatable' => false],
        'createdAt'             => ['type' => 'single', 'value' => 'created_at', 'updatable' => false],
        'expiresAt'             => ['type' => 'single', 'value' => 'expire_at', 'updatable' => false],
        'state'                 => ['type' => 'single', 'value' => 'active', 'updatable' => false],
        'value'                 => ['type' => 'single', 'value' => 'type.value', 'updatable' => false],
        'prices.id'             => ['type' => 'collection.single', 'value' => 'Prices.id', 'updatable' => false],        
        'prices.translations'   => ['type' => 'collection.single', 'value' => 'Prices.Translation', 'updatable' => false],        
        'prices.value'          => ['type' => 'collection.single', 'value' => 'Prices.value', 'updatable' => false], 
                
                
    ];
      
    
    public function buildInitialQuery() {
        
         $q = Doctrine_Query::create()
            ->from('Product p')
        ;
        return $q;
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
                //->reformat($entity['product_declination']['translations'])
                //->reformat($entity['product_category']['translations'])
        ;

        // imageURL
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
        $entity['imageUrl'] = url_for('@os_api_picture?id=' . $entity['id']);
        
        // currency
        $currency = sfConfig::get('project_internals_currency', ['iso' => 978, 'symbol' => '€']);
        $entity['currencyCode'] = $currency['iso'];

        
        return $entity;
     }

}
