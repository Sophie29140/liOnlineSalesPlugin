<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of actions
 *
 * @author Sophie MICHEL <sophie.michel@libre-informatique.fr>
 */
class osApiPromotionsActions extends apiActions
{

    public function getMyService()
    {
          return $this->getService('api_promotions_service');
    }
}
