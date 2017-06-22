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
class ApiEventsService extends ApiEntityService
{
    protected $translationService;
    protected $oauth;
    protected static $FIELD_MAPPING = [
        'id'            => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        //'metaEvent'     => ['type' => 'sub-record', 'value' => null],
        'metaEvent.id'  => ['type' => 'single', 'value' => 'MetaEvent.id', 'for-update' => 'meta_event_id'],
        'metaEvent.translations' => ['type' => 'collection', 'value' => 'MetaEvent.Translation', 'updatable' => false],
        'category'      => ['type' => 'single', 'value' => 'EventCategory.name', 'updatable' => false],
        'translations'  => ['type' => 'collection', 'value' => 'Translation'],
        'imageId'       => ['type' => 'single', 'value' => 'picture_id'],
        'imageURL'      => ['type' => null, 'value' => null, 'updatable' => false],
        'manifestations'=> ['type' => 'value', 'value' => [], 'updatable' => false],

    ];

    /**
     * @var $manifestationsService
     */
    protected $manifestationsService;


    public function buildInitialQuery()
    {
        return parent::buildInitialQuery()
            ->leftJoin('root.Manifestations Manifestations')
        ;
    }

    protected function postFormatEntity(array $entity, Doctrine_Record $record)
    {
        // translations
        $this->translationService
                ->reformat($entity['translations'])
                ->reformat($entity['metaEvent']['translations']);

        // imageURL
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
        $entity['imageURL'] = url_for('@os_api_pictures_resource?id=' . $entity['id']);

        // manifestations
        $query = [
            'criteria' => [
                'event.id' => [
                    'type' => 'equal',
                    'value' => $entity['id'],
                ],
                'happens_at' => [
                    'type' => 'greater',
                    'value' => date('Y-m-d H:i:s'),
                ],
            ],
            'limit' => 100,
            'sorting' => [],
            'page' => 1,
        ];
        
        $entity['manifestations'] = $this->manifestationsService->findAll($query);

        return $entity;
    }

    public function setApiManifestationsService(ApiManifestationsService $manifestations)
    {
        $this->manifestationsService = $manifestations;
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
    
    public function getBaseEntityName()
    {
        return 'Event';
    }
}
