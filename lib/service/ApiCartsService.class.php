<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiCartsService
 *
 * @author Glenn Cavarlé <glenn.cavarle@libre-informatique.fr>
 */
class ApiCartsService extends ApiEntityService
{

    protected static $FIELD_MAPPING = [
        'id'            => ['type' => 'single', 'value' => 'id'],
        'items'         => ['type' => null, 'value' => null],
        'itemsTotal'    => ['type' => null, 'value' => null],
        'total'         => ['type' => null, 'value' => null],
        'customer'      => ['type' => null, 'value' => null],
        'currencyCode'  => ['type' => null, 'value' => null],
        'localeCode'    => ['type' => null, 'value' => null],
        'checkoutState' => ['type' => null, 'value' => null],
    ];

    /**
     * @var liApiOAuthService
     */
    protected $oauth;

    /**
     * @var ApiCartItemsService
     */
    protected $cartItemsService;

    /**
     * @var ApiCustomersService
     */
    protected $customersService;

    /**
     * @param ApiOAuthService $service
     */
    public function setOAuthService(ApiOAuthService $service)
    {
        $this->oauth = $service;
    }

    /**
     * @param ApiCartItemsService $service
     */
    public function setCartItemsService(ApiCartItemsService $service)
    {
        $this->cartItemsService = $service;
    }

    /**
     * @param ApiCustomersService $service
     */
    public function setCustomersService(ApiCustomersService $service)
    {
        $this->customersService = $service;
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
        // customer
        $entity['customer'] = new ArrayObject;
        if ( false ) // TODO: remove and make it work
        if ($record->contact_id) {
            $entity['customer'] = $this->customersService->findOneById($record->contact_id);
        }
        
        $entity['checkoutState'] = $record->Order->count() == 0 ? 'fullfilled' : 'cart';
        $cultures = array_keys(sfConfig::get('project_internals_cultures', ['fr' => 'Français']));
        $entity['localeCode']    = array_shift($cultures);

        $entity['items'] = [];
        $entity['itemsTotal'] = 0;
        // cart items
        $query = [
            'limit'    => 100, // TODO
            'sorting'  => [],
            'page'     => 1,
        ];
        $entity['items'] = $this->cartItemsService->findAll($record->id, $query);

        // totals
        $entity['itemsTotal'] = 0;
        foreach ($entity['items'] as $item) {
            $entity['itemsTotal'] += $item['total'];
        }

        $entity['adjustments'] = [];  // TODO

        $entity['adjustmentsTotal'] = 0;
        foreach($entity['adjustments'] as $adjustment) {
            $entity['adjustmentsTotal'] += $adjustment['amount'];
        }

        $entity['total'] = $entity['itemsTotal'] + $entity['adjustmentsTotal'];

        // currency
        $currency = sfConfig::get('project_internals_currency', ['iso' => 978, 'symbol' => '€']);
        $entity['currencyCode'] = $currency['iso'];

        return $entity;
    }

    /**
     *
     * @param int $cart_id
     * @return boolean
     */
    public function deleteCart($cart_id)
    {
        return false;
    }

   /**
     *
     * @param int $cartId
     * @param array $data
     * @return boolean
     */
    public function updateCart($cartId, $data)
    {
        // Check existence and access
        $cart = $this->findOneById($cartId);
        if (count($cart) == 0) {
            return false;
        }

        // Validate data
        if (!is_array($data)) {
            return false;
        }
        if (isset($data['checkoutState']) && $data['checkoutState'] != 'new') {
            return false;
        }

        if (isset($data['checkoutState'])) {
            $cart = Doctrine::getTable('Transaction')->find($cartId);
            $cart->checkout_state = $data['checkoutState'];
            $cart->save();
        }

        return true;
    }

    /**
     * @param integer $cartId
     * @return boolean
     */
    public function isCartEditable($cartId)
    {
        $cart = $this->findOneById($cartId);
        if ( 0 == count($cart) ) {
            return false;
        }
        if ($cart['checkoutState'] != 'cart') {
            return false;
        }
        return true;
    }

    public function buildInitialQuery()
    {
        $token = $this->oauth->getToken();
        return Doctrine_Query::create()
            ->from('Transaction root')
            ->leftJoin('root.Professional Professional')
            ->leftJoin('root.OsToken token')
            ->andWhere('token.token = ?', $token->token);
        
    }
}
