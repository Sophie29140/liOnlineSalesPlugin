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
        'id'             => ['type' => 'single', 'value' => 'id'],
        'payment_method' => ['type' => 'single', 'value' => 'Method.name'],
        'amount'         => ['type' => 'single', 'value' => 'value'],
        'orderId'        => ['type' => 'single', 'value' => 'transaction_id'],
        'state'          => ['type' => null,     'value' => null],
        'createAt'       => ['type' => 'single', 'value' => 'created_at'],
        '_link.order'    => ['type' => null,     'value' => null],
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
     *
     * @param array $query
     * @return array
     */
    public function findAll($query)
    {
        $q = $this->buildQuery($query);
        $cartDotrineCol = $q->execute();

        return $this->getFormattedEntities($cartDotrineCol);
    }

    /**
     *
     * @param int $id
     * @return array | null
     */
    public function findOneById($id)
    {
        $token = $this->oauth->getToken();
        $query = [
            'criteria' => [
                'id' => [
                    'value' => $id,
                    'type'  => 'equal',
                ],
            ]
        ];
        $dotrineRec = $this->buildQuery($query)
            ->fetchOne();

        if (false === $dotrineRec) {
            return new ArrayObject;
        }

        return $this->getFormattedEntity($dotrineRec);
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
        return Doctrine_Query::create()
            ->from('Payment root')
            ->leftJoin('root.Transaction Transaction')
            ->leftJoin('Transaction.OsToken token')
            ->andWhere('token.id = ?', $token->id)
        ;
    }
}
