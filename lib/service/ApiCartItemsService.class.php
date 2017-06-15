<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiCartItemsService
 *
 * @author Glenn Cavarlé <glenn.cavarle@libre-informatique.fr>
 */
// TODO
class ApiCartItemsService extends ApiEntityService
{

     protected static $FIELD_MAPPING = [
        'id'               => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'type'             => ['type' => null, 'value' => null, 'updatable' => false],
        'quantity'         => ['type' => null, 'value' => null, 'updatable' => false],
        'declination.id'   => ['type' => 'single', 'value' => 'gauge_id'],
        'declination.code' => ['type' => 'single', 'value' => 'Declination.code', 'updatable' => false],
        'declination.position' => ['type' => null, 'value' => null, 'updatable' => false],
        'declination.translations' => ['type' => 'collection', 'value' => 'Gauge.Workspace.Translation', 'updatable' => false],
        'unitPrice'        => ['type' => 'single', 'value' => 'value', 'updatable' => false],
        'total'            => ['type' => null, 'value' => null, 'updatable' => false],
        'units.id'         => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'units.adjustments.id'   => ['type' => null, 'value' => null, 'updatable' => false],
        'units.adjustments.type'   => ['type' => 'value', 'value' => 'taxes', 'updatable' => false],
        'units.adjustments.label'  => ['type' => 'value', 'value' => 'Taxes', 'updatable' => false],
        'units.adjustments.amount' => ['type' => 'single', 'value' => 'taxes', 'updatable' => false],
        'units.adjustmentsTotal'   => ['type' => 'single', 'value' => 'taxes', 'updatable' => false],
        'unitsTotal'       => ['type' => null, 'value' => null, 'updatable' => false],
        'adjustments'      => ['type' => null, 'value' => null, 'updatable' => false],
        'adjustmentsTotal' => ['type' => null, 'value' => null, 'updatable' => false],
        '_link.product'    => ['type' => null, 'value' => null, 'updatable' => false],
        '_link.order'      => ['type' => null, 'value' => null, 'updatable' => false],
        'rank'             => ['type' => null, 'value' => null, 'updatable' => false],
        'state'            => ['type' => null, 'value' => null, 'updatable' => false],
     ];

    /**
     * @var liApiOAuthService
     */
    protected $oauth;

    /**
     * @var ApiManifestationsService
     */
    protected $manifestationsService;
    
    protected $type = 'ticket';
    
    public function setType($type)
    {
        return $this;
    }
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param ApiOAuthService $service
     */
    public function setOAuthService(ApiOAuthService $service)
    {
        $this->oauth = $service;
    }

    /**
     * @param ApiManifestationsService $service
     */
    public function setManifestationsService(ApiManifestationsService $service)
    {
        $this->manifestationsService = $service;
    }

    /**
     * @param array $entity
     * @param Doctrine_Record $record
     * @return array
     */
    protected function postFormatEntity(array $entity, Doctrine_Record $record)
    {
        $types = ['Ticket' => 'ticket', 'BoughtProduct' => 'product', 'MemberCard' => 'pass'];
        $entity['type'] = !isset($types[get_class($record)]) ? $types['BoughtProduct'] : $types[get_class($record)];

        $entity['unitsTotal'] = 0;
        $entity['adjustmentsTotal'] = 0;
        $entity['quantity'] = 1;
        $entity['unitPrice'] = round($entity['unitPrice']/(1+$record->vat),2);
        
        $entity['adjustments'] = [];
        $entity['adjustmentsTotal'] = 0;
        $entity['units'] = [$entity['units']];
        foreach ( $entity['units'] as $u => &$unit ) {
            if ( $unit['adjustments']['amount'] == 0 ) {
                $unit['adjustments'] = [];
            }
            else {
                $entity['adjustments'][] = $unit['adjustments'];
                $entity['adjustmentsTotal'] += $unit['adjustments']['amount'];
                $unit['adjustments'] = [$unit['adjustments']];
            }
            
            if ( $record->vat != 0 ) {
                $unit['adjustments'][] = $adj = [
                    'id' => null,
                    'type' => 'vat',
                    'label' => 'VAT '.($record->vat*100).'%',
                    'amount' => $record->value - $entity['unitPrice'],
                ];
                $unit['adjustmentsTotal'] += $adj['amount'];
                $entity['adjustmentsTotal'] += $adj['amount'];
                $entity['adjustments'][] = $adj;
            }
        }

        $entity['unitsTotal'] = $entity['unitPrice'] * $entity['quantity'];
        $entity['total'] = $entity['unitsTotal'] + $entity['adjustmentsTotal'];
        
        sfContext::getInstance()->getConfiguration()->loadHelpers(['Url']);
        switch ( $entity['type'] ) {
            case 'product':
            case 'pass':
                // TODO
            default:
                $entity['_link']['product'] = url_for('@os_api_manifestations_resource?id='.$record->manifestation_id);
                $entity['_link']['order'] = url_for('@os_api_orders_resource?id='.$record->transaction_id);
                break;
        }
        
        $entity['state'] = $record->isSold() ? 'sold' : null;
        
        return $entity;
    }
    
    /**
     *
     * @param int $cart_id
     * @param int $query
     * @return array
     */
    public function findAll($cart_id, $query)
    {
        $dotrineCol = $this->buildQuery($query)
            ->andWhere('root.transaction_id = ?', $cart_id)
            ->execute()
        ;

        return $this->getFormattedEntities($dotrineCol);
    }

    /**
     *
     * @param int $cartId
     * @param int $itemId
     * @return array|null
     */
    public function findOne($cartId, $itemId)
    {
        $query = [
            'criteria' => [
                'id' => [
                    'value' => (int)$itemId,
                    'type'  => 'equal',
                ],
            ]
        ];
        $dotrineRec = $this->buildQuery($query)
            ->andWhere('root.transaction_id = ?', (int)$cartId)
            ->fetchOne()
        ;

        if (false === $dotrineRec) {
            return new ArrayObject;
        }

        return $this->getFormattedEntity($dotrineRec);
    }

    /**
     *
     * @param int $cartId
     * @param array $data
     * @return array
     * @throws liApiException
     */
    public function create($cartId, $data)
    {
        // Check type
        if (!isset($data['type'])) {
            throw new liApiException('Missing type parameter');
        }
        $type = $data['type'];
        $allowedTypes = ['ticket', 'product', 'pass'];
        if (!in_array($type, $allowedTypes)) {
            throw new ocNotImplementedException(sprintf('Wrong type parameter: %s. Expected one of: ', $type, implode(',', $allowedTypes)));
        }
        if ($type != 'ticket') {
            // TODO...
            throw new liApiNotImplementedException('Not implemented yet for type: ' . $type);
        }

        if (!isset($data['priceId'])) {
            throw new liApiException('Missing priceId parameter');
        }
        $priceId = (int)$data['priceId'];

        if (!isset($data['declinationId'])) {
            throw new liApiException('Missing declinationId parameter');
        }
        $declinationId = (int)$data['declinationId'];

        if ( !$this->checkGaugeAndPriceAccess($declinationId, $priceId) ) {
            throw new liApiException('Invalid value for priceId or declinationId parameter');
        }

        if ( !$this->checkGaugeAvailability($declinationId) ) {
            throw new liApiException('Gauge is full or not available in your context');
        }
        
        switch ( $data['type'] ) {
            case 'product':
            case 'pass':
                // TODO
            default:
                $cartItem = new Ticket;
                $cartItem->transaction_id = $cartId;
                $cartItem->price_id = $priceId;
                $cartItem->gauge_id = $declinationId;
                $cartItem->save();
                break;
        }

        return $this->getFormattedEntity($cartItem);
    }


    public function checkGaugeAvailability($gaugeId)
    {
        $gauge = Doctrine::getTable('Gauge')->find($gaugeId);
        if (!$gauge) {
            return false;
        }
        if ($gauge->free <= 0) {
            return false;
        }
        return true;
    }

    /**
     * @param int $gaugeId
     * @param int $priceId
     * @return boolean
     */
    public function checkGaugeAndPriceAccess($gaugeId, $priceId)
    {
        return true; // TODO remove this line and use the manifestationsService
        $count = $this->manifestationsService->buildQuery([])
            ->andWhere('g.id = ?', $gaugeId)
            ->andWhere('(FALSE')
            ->orWhere('pmp.id = ?', $priceId)
            ->orWhere('pgp.id = ?', $priceId)
            ->orWhere('FALSE)')
            ->count()
        ;

        return $count > 0;
    }

    /**
     *
     * @param int $cartId
     * @param int $itemId
     * @param array $data
     * @return boolean
     */
    public function updateCartItem($cartId, $itemId, $data)
    {
        $type = !isset($data['type']) ? 'ticket' : $data['type'];
        
        // Update cart item
        switch($type) {
            case 'ticket':
                $success = $this->updateTicketCartItem($itemId, $data);
                break;
            case 'product':
            case 'pass':
                // TODO: update other kind of cart items (not ticket)
                // TODO ... $success = $this->updatePassCartItem($itemId, $data);
                $success = false;
                break;
            default:
                $success = false;
        }

        return $success;
    }

    /**
     * @param int $itemId
     * @param array $data
     * @return boolean   true if successful, false if failed
     */
    private function updateTicketCartItem($itemId, $data)
    {
        // Validate data
        if (!is_array($data)) {
            return false;
        }

        $cartItem = Doctrine::getTable('Ticket')->find($itemId);
        if (!$cartItem) {
            return false;
        }
        
        $accessor = new liApiPropertyAccessor;
        $cartItem = $accessor->toRecord($data, $cartItem, static::$FIELD_MAPPING);

        if (isset($data['quantity'])) {
            if ( (int)$data['quantity'] != 1 ) {
                return false;
            }
        }

        $cartItem->save();
        return true;
    }

    /**
     *
     * @param int $cartId
     * @param int $itemId
     * @return boolean   true if successful, false if failed
     */
    public function deleteCartItem($cartId, $itemId, $type)
    {
        // Update cart item
        switch($type) {
            case 'ticket':
                $success = $this->deleteTicketCartItem($itemId);
                break;
            case 'product':
            case 'pass':
                // TODO: delete other kind of cart items (not ticket)
                // TODO ... $success = $this->deletePassCartItem($itemId);
                $success = false;
                break;
            default:
                $success = false;
        }

        return $success;
    }


    /**
     * @param int $itemId
     * @return boolean   true if successful, false if failed
     */
    private function deleteTicketCartItem($itemId)
    {
        $item = Doctrine::getTable('Ticket')->find($itemId);
        if (!$item) {
            return false;
        }
        
        if ( $item->isSold() ) {
            return false;
        }

        return $item->delete();
    }


    /**
     * @param int $cartId
     * @param int $itemId
     * @return boolean
     */
    public function isCartItemEditable($cartId, $itemId)
    {
        // Check existence and access
        $item = $this->findOne($cartId, $itemId);
        if (count($item) == 0) {
            return false;
        }
        
        if ( $item['state'] == 'sold' ) {
            return false;
        }

        return true;
    }

    /**
     * @param int $cartId
     * @param int $declinationId
     * @return boolean
     */
    public function isCartItemCreatable($cartId, $declinationId)
    {
        return true;
    }

    /**
     * @return @return Doctrine_Query
     */
    public function buildInitialQuery()
    {
        // TODO: take into account the type of item targeted
        $token = $this->oauth->getToken();
        
        switch ( $this->type ) {
            case 'product':
            case 'pass':
                // TODO
                return false;
            default:
                return Doctrine_Query::create()
                    ->from('Ticket root')
                    ->leftJoin('root.Price Price')
                    ->leftJoin('root.Transaction Transaction')
                    ->leftJoin('Transaction.OsToken token')
                    ->andWhere('token.token = ?', $token->token)
                ;
        }
    }
}
