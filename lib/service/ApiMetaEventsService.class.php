<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiCustomersService
 *
 * @author Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class ApiMetaEventsService extends ApiEntityService
{
    protected $translationService;
    protected $oauth;
    protected static $FIELD_MAPPING = [
        'id'                => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'translations.lang' => ['type' => 'collection.single', 'value' => 'Translation.lang'],
        'translations.name' => ['type' => 'collection.single', 'value' => 'Translation.name'],
        'translations.description' => ['type' => 'collection.single', 'value' => 'Translation.description'],
    ];

    public function getBaseEntityName()
    {
        return 'MetaEvent';
    }

    protected function postFormatEntity(array $entity, Doctrine_Record $record)
    {
        // translations
        $this->translationService
                ->reformat($entity['translations']);
        return $entity;
    }

    public function setTranslationService(ApiTranslationService $i18n)
    {
        $this->translationService = $i18n;
        return $this;
    }

    public function setOAuthService(ApiOAuthService $service)
    {
        $this->oauth = $service;
    }

    public function getOAuthService()
    {
        return $this->oauth;
    }
}
