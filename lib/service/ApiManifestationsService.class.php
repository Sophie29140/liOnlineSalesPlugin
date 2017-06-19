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
        'id'                => ['type' => 'single', 'value' => 'id'],
        'startsAt'          => ['type' => 'single', 'value' => 'happens_at'],
        'endsAt'            => ['type' => 'single', 'value' => 'ends_at'],
        'event.id'              => ['type' => 'single', 'value' => 'Event.id'],
        //'event.metaEvent'       => ['type' => 'sub-record', 'value' => null],
        'event.metaEvent.id'    => ['type' => 'single', 'value' => 'Event.MetaEvent.id'],
        'event.metaEvent.translations' => ['type' => 'collection', 'value' => 'Event.MetaEvent.Translation'],
        'event.category'        => ['type' => 'single', 'value' => 'Event.EventCategory.name'],
        'event.translations'    => ['type' => 'collection', 'value' => 'Event.Translation'],
        'event.imageId'         => ['type' => 'single', 'value' => 'Event.picture_id'],
        'event.imageURL'        => ['type' => null, 'value' => null],
        //'location'          => ['type' => null, 'value' => null],
        'location.id'       => ['type' => 'single', 'value' => 'Location.id'],
        'location.name'     => ['type' => 'single', 'value' => 'Location.name'],
        'location.address'  => ['type' => 'single', 'value' => 'Location.address'],
        'location.zip'      => ['type' => 'single', 'value' => 'Location.postalcode'],
        'location.city'     => ['type' => 'single', 'value' => 'Location.city'],
        'location.country'  => ['type' => 'single', 'value' => 'Location.country'],
        //'gauges'            => ['type' => 'collection', 'value' => null],
        'gauges.id'         => ['type' => 'collection.single', 'value' => 'Gauges.id'],
        'gauges.name'       => ['type' => 'collection.single', 'value' => 'Gauges.Workspace.name'],
        'gauges.availableUnits' => ['type' => 'collection.single', 'value' => 'Gauges.free'],
        //'gauges.prices.id' => ['type' => 'single', 'value' => 'Gauges.Prices.id'],
        //'gauges.prices.translations' => ['type' => 'single', 'value' => 'Gauges.Prices.Translation'],
        //'gauges.prices.value' => ['type' => 'single', 'value' => 'Gauges.Prices.value'],
        //'gauges.prices.currencyCode' => null,
    ];

    public function buildInitialQuery()
    {
      $q = $this->manifestationsService->buildQuery($this->oauth->getToken()->OsApplication->User, NULL, 'root');
      // TODO: use the customer API service when it will be validated
//        $q = $this->manifestationsService->completeQueryWithContact($q, $this->oauth->getToken()->OcTransaction[0]->oc_professional_id
//            ? $this->oauth->getToken()->OcTransaction[0]->OcProfessional->Professional->contact_id
//            : NULL
//        );
      return $q;
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

