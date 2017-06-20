<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiPicturesService
 *
 * @author Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class ApiPicturesService extends ApiEntityService
{

    protected static $FIELD_MAPPING = [
        'id'            => ['type' => 'single', 'value' => 'id', 'updatable' => false],
        'name'          => ['type' => 'single', 'value' => 'name'],
        'type'          => ['type' => 'single', 'value' => 'type', 'updatable' => false],
        'width'         => ['type' => 'single', 'value' => 'width', 'updatable' => false],
        'height'        => ['type' => 'single', 'value' => 'height', 'updatable' => false],
    ];

    /**
     * @var liApiOAuthService
     */
    protected $oauth;

    /**
     * @param ApiOAuthService $service
     */
    public function setOAuthService(ApiOAuthService $service)
    {
        $this->oauth = $service;
    }
    
    public function getBaseEntityName()
    {
        return 'Picture';
    }
}
