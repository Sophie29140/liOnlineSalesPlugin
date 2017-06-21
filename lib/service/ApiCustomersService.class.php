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
class ApiCustomersService  extends ApiEntityService {
    
    protected $translationService;
    protected $oauth;
    
    protected static $HIDDEN_FIELD_MAPPING = [
        'password'               => ['type' => 'single', 'value' => 'Contact.password'],
    ];
    
    protected static $FIELD_MAPPING = [
        'id'                     => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'email'                  => ['type' => 'single', 'value' => 'email', 'updatable' => true],
        'firstname'              => ['type' => 'single', 'value' => 'firstname', 'updatable' => true],
        'lastname'               => ['type' => 'single', 'value' => 'name', 'updatable' => true],
        'shortname'              => ['type' => 'single', 'value' => 'shortname', 'updatable' => true],
        'address'                => ['type' => 'single', 'value' => 'address', 'updatable' => true],
        'zip'                    => ['type' => 'single', 'value' => 'postalcode', 'updatable' => true],
        'city'                   => ['type' => 'single', 'value' => 'city', 'updatable' => true], 
        'country'                => ['type' => 'single', 'value' => 'country', 'updatable' => true],        
        'phoneNumber'            => ['type' => 'collection', 'value' => 'Phonenumbers.number', 'updatable' => true],        
        'datesOfBirth'           => ['type' => 'collection', 'value' => 'YOBs.year', 'updatable' => true],        
        'locale'                 => ['type' => 'single', 'value' => 'culture', 'updatable' => true],        
        'uid'                    => ['type' => 'single', 'value' => 'vcard_uid'],
        'subscribedToNewsletter' => ['type' => 'single', 'value' => '!contact_email_no_newsletter'],    
                
                
    ];
      
    
    public function buildInitialQuery()
    {
           return parent::buildInitialQuery()
        ;
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
       
        return $entity;
     }

    public function getBaseEntityName() 
    {
        return 'Contact';
    }

}
