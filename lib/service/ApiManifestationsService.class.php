<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiManifestationService
 *
 * @author Glenn Cavarlé <glenn.cavarle@libre-informatique.fr>
 * @author Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class ApiManifestationsService extends ApiEntityService
{

    protected $translationService;
    protected $manifestationsService;
    protected $oauth;

    protected static $FIELD_MAPPING = [
        'id'                => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'startsAt'          => ['type' => 'single', 'value' => 'happens_at', 'updatable' => true],
        'endsAt'            => ['type' => 'single', 'value' => 'ends_at', 'updatable' => true],
        'event_id'              => ['type' => 'single', 'value' => 'event_id', 'updatable' => true],
        //'event.metaEvent'       => ['type' => 'sub-record', 'value' => null],
        'event.metaEvent.id'    => ['type' => 'single', 'value' => 'Event.MetaEvent.id', 'updatable' => false],
        'event.metaEvent.translations' => ['type' => 'collection', 'value' => 'Event.MetaEvent.Translation', 'updatable' => false],
        'event.category'        => ['type' => 'single', 'value' => 'Event.EventCategory.name', 'updatable' => false],
        'event.translations'    => ['type' => 'collection', 'value' => 'Event.Translation', 'updatable' => false],
        'event.imageId'         => ['type' => 'single', 'value' => 'Event.picture_id', 'updatable' => false],
        'event.imageURL'        => ['type' => null, 'value' => null, 'updatable' => false],
        //'location'          => ['type' => null, 'value' => null],
        'location_id'       => ['type' => 'single', 'value' => 'location_id', 'updatable' => true],
        'location.name'     => ['type' => 'single', 'value' => 'Location.name', 'updatable' => false],
        'location.address'  => ['type' => 'single', 'value' => 'Location.address', 'updatable' => false],
        'location.zip'      => ['type' => 'single', 'value' => 'Location.postalcode', 'updatable' => false],
        'location.city'     => ['type' => 'single', 'value' => 'Location.city', 'updatable' => false],
        'location.country'  => ['type' => 'single', 'value' => 'Location.country', 'updatable' => false],
        //'gauges'            => ['type' => 'collection', 'value' => null],
        'gauges.id'         => ['type' => 'collection.single', 'value' => 'Gauges.id', 'updatable' => false],
        'gauges.name'       => ['type' => 'collection.single', 'value' => 'Gauges.Workspace.name', 'updatable' => false],
        'gauges.availableUnits' => ['type' => 'collection.single', 'value' => 'Gauges.free', 'updatable' => false],
        //'gauges.prices.id' => ['type' => 'single', 'value' => 'Gauges.Prices.id'],
        //'gauges.prices.translations' => ['type' => 'single', 'value' => 'Gauges.Prices.Translation'],
        //'gauges.prices.value' => ['type' => 'single', 'value' => 'Gauges.Prices.value'],
        //'gauges.prices.currencyCode' => null,
    ];


    public function buildInitialQuery()
    {
        return parent::buildInitialQuery();
    }

    public function getMaxShownAvailableUnits()
    {
        return 10;
    }

    protected function postFormatEntity(array $entity, Doctrine_Record $manif)
    {
        // translations & timestamps
        $this->translationService
            ->reformat($entity['event']['translations'])
            ->reformat($entity['event']['metaEvent']['translations'])
            ->reformat($entity);

        // gauges
        $currency = sfConfig::get('project_internals_currency', ['iso' => 978, 'symbol' => '€']);
        foreach ( $entity['gauges'] as $id => $gauge ) {
            // availableUnits
            if ( isset($gauge['availableUnits']) ) {
                $free = $gauge['availableUnits'];
                $entity['gauges'][$id]['availableUnits'] = $free > $this->getMaxShownAvailableUnits()
                    ? $this->getMaxShownAvailableUnits()
                    : $free;
            }

            // gauges.prices
            $entity['gauges'][$id]['prices'] = [];
            foreach ( ['PriceManifestations' => $manif, 'PriceGauges' => $manif->Gauges[$id]] as $collection => $object )
            foreach ( $object->$collection as $pm ) { // prices from manifestation
                $price = [
                    'id' => $pm->price_id,
                    'value' => $pm->value,
                    'currencyCode' => $currency['iso'],
                ];
                $price['translations'] = [];
                if ( $pm->price_id ) {
                    foreach ( $pm->Price->Translation as $i11n ) {
                        $price['translations'][$i11n->lang] = [];
                        $price['translations'][$i11n->lang]['name'] = $i11n->name;
                        $price['translations'][$i11n->lang]['description'] = $i11n->description;
                    }
                }
                $entity['gauges'][$id]['prices'][] = $price;
            }
        }

        // imageURL
        if ( $entity['event']['imageId'] ) {
            sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
            $entity['event']['imageURL'] = url_for('@os_api_picture?id='.$entity['event']['imageId']);
        }

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

    public function setManifestationsService(ManifestationsService $service)
    {
        $this->manifestationsService = $service;
    }

    public function getOAuthService()
    {
        return $this->oauth;
    }
  
    public function getBaseEntityName() 
    {
        return 'Manifestation';
    }

}

