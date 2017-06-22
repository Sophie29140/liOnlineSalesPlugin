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
class ApiCustomersService extends ApiEntityService
{
    protected static $HIDDEN_FIELD_MAPPING = [
        'password'      => ['type' => 'single', 'value' => 'password'],
    ];

    protected static $FIELD_MAPPING = [
        'id'            => ['type' => 'single', 'value' => 'id'],
        'email'         => ['type' => 'single', 'value' => 'email'],
        'firstName'     => ['type' => 'single', 'value' => 'firstname'],
        'lastName'      => ['type' => 'single', 'value' => 'name'],
        'shortName'     => ['type' => 'single', 'value' => 'shortname'],
        'address'       => ['type' => 'single', 'value' => 'address'],
        'zip'           => ['type' => 'single', 'value' => 'postalcode'],
        'city'          => ['type' => 'single', 'value' => 'city'],
        'country'       => ['type' => 'single', 'value' => 'country'],
        'phoneNumber'   => ['type' => 'single', 'value' => 'Phonenumbers.number'],
        'datesOfBirth'  => ['type' => null    , 'value' => null],
        'locale'        => ['type' => 'single', 'value' => 'culture'],
        'uid'           => ['type' => 'single', 'value' => 'vcard_uid'],
        'subscribedToNewsletter' => ['type' => 'single', 'value' => '!email_no_newsletter'],
    ];

    /**
     * @var ocApiOAuthService
     */
    protected $oauth;

    /**
     * @return boolean
     */
    public function isIdentificated()
    {
        $token = $this->getOAuthService()->getToken();
        return $token instanceof OsToken && $token->Transaction->contact_id !== null;
    }

    /**
     *
     * @return NULL|boolean  NULL if no email nor password given, else boolean
<     */
    public function identify(array $query)
    {
        // prerequisites
        if (!(isset($query['criteria']['password']) && $query['criteria']['password'] && isset($query['criteria']['password']['value'])
            && isset($query['criteria']['email']) && $query['criteria']['email'] && isset($query['criteria']['email']['value']))) {
            return null;
        }

        // encrypt password
        $serviceName = sfConfig::get('project_password_encryption_service', 'password_plain_text_service');
        $encryptionService = sfContext::getInstance()->getContainer()->get($serviceName);
        $salt = sfConfig::get('project_password_salt', '');
        $query['criteria']['password']['value'] = $encryptionService->encrypt($query['criteria']['password']['value'], $salt);

        if (!($contact = $this->buildQuery($query)->fetchOne()) instanceof Contact) {
            return false;
        }
        
        $token = $this->getOAuthService()->getToken();
        $token->Transaction->Contact = $contact;
        $token->Transaction->save();
        return true;
    }

    /**
     *
     * @return boolean  true if the logout was possible, false if nobody is identified
     */
    public function logout()
    {
        if (!$this->isIdentificated()) {
            return false;
        }

        $token = $this->getOAuthService()->getToken();

        $token->Transaction = new Transaction;
        $token->save();

        return true;
    }

    public function update($id, array $data)
    {
        if (!$this->isIdentificated()) {
            return false;
        }
        unset($data['id'], $data['email']);
        
        return parent::update($id, $data);
    }

    /**
     *
     * @return NULL|Contact
     */
    public function getIdentifiedContact()
    {
        if (!$this->isIdentificated()) {
            return null;
        }
        return $this->getOAuthService()->getToken()->Transaction->Contact;
    }

    /**
     *
     * @return array
     */
    public function getIdentifiedCustomer()
    {
        $contact = $this->getIdentifiedContact();
        if (!$contact) {
            return false;
        }
        return $this->getFormattedEntity($contact);
    }

    public function setOAuthService(ApiOAuthService $service)
    {
        $this->oauth = $service;
        return $this;
    }

    public function getOAuthService()
    {
        return $this->oauth;
    }
    
    public function getBaseEntityName()
    {
        return 'Contact';
    }
}
