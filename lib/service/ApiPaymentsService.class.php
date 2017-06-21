<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiPaymentsService
 *
 * @author Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class ApiPaymentsService extends ApiEntityService
{
    protected static $FIELD_MAPPING = [
        'id'             => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'payment_method' => ['type' => 'single', 'value' => 'Method.name', 'updatable' => false],
        'amount'         => ['type' => 'single', 'value' => 'value', 'updatable' => false],
        'orderId'        => ['type' => 'single', 'value' => 'transaction_id', 'updatable' => false],
        'state'          => ['type' => null,     'value' => null, 'updatable' => false],
        'createAt'       => ['type' => 'single', 'value' => 'created_at', 'updatable' => false],
        '_link.order'    => ['type' => null,     'value' => null, 'updatable' => false],
    ];

    /**
     * @var ApiOAuthService
     */
    protected $oauth;

    /**
     * @var ApiTranslationService
     */
    protected $translationService;

    /**
     * @param ApiOAuthService $service
     */
    public function setOAuthService(ApiOAuthService $service)
    {
        $this->oauth = $service;
    }

    /**
     * @param ApiTranslationService $service
     */
    public function setTranslationService(ApiTranslationService $service)
    {
        $this->translationService = $service;
    }

    
    /**
     * @param array $entity
     * @param Doctrine_Record $record
     * @return array
     */
    protected function postFormatEntity(array $entity, Doctrine_Record $record)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(['Url']);
        
        $entity['state'] = 'completed';
        $entity['_link']['order'] = url_for('@os_api_orders_resource?id='.$entity['orderId']);
        $this->translationService->reformat($entity);
        
        return $entity;
    }

    public function buildInitialQuery()
    {
        $token = $this->oauth->getToken();
        return parent::buildInitialQuery()
            ->leftJoin('root.Transaction Transaction')
            ->leftJoin('Transaction.OsToken token')
            ->andWhere('token.id = ?', $token->id)
        ;
    }

    public function getBaseEntityName() 
    {
        return 'Payment';
    }

}
