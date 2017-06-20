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
class ApiEventCategoriesService extends ApiEntityService {

    protected $oauth;
    protected static $FIELD_MAPPING = [
        'id'                => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'name'              => ['type' => 'single', 'value' => 'name'],
    ];

    public function getBaseEntityName()
    {
        return 'EventCategory';
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
