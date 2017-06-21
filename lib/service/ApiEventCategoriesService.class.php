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
class ApiEventCategoriesService  extends ApiEntityService {
    
    protected $translationService;
    protected $oauth;
    protected static $FIELD_MAPPING = [
        'id'            => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'name'          => ['type' => 'single', 'value' => 'name', 'updatable' => false],
                
    ];
      
    
    public function buildInitialQuery() {
        
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
    
    public function setEventCategoryService(EventCategoryService $service)
    {
        $this->eventsCategoryService = $service;
    }
    public function getMaxShownAvailableUnits()
    {
        return 10;
    }
    protected function postFormatEntity(array $entity, Doctrine_Record $product) {
      
       return $entity;
     }

    public function getBaseEntityName() 
    {
        return 'EventCategory';
    }

}
