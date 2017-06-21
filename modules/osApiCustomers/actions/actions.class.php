<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of actions
 *
 * @author Glenn Cavarlé <glenn.cavarle@libre-informatique.fr>
 * @author Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class osApiCustomersActions extends apiActions
{

    /**
     * @return ApiEntityService
     */
    public function getMyService()
    {
        return $this->getService('api_customers_service');
    }

    public function executeLogout(sfWebRequest $request)
    {
        $customers = $this->getService('api_customers_service');
        if ( $customers->logout() ) {
            return $this->createJsonResponse([
                'code' => ApiHttpStatus::SUCCESS,
                'message' => 'Logout successful',
            ]);
        }
        return $this->createJsonResponse([
            'code' => ApiHttpStatus::BAD_REQUEST,
            'message' => 'Logout failed',
        ]);

    }

    public function executeLogin(sfWebRequest $request)
    {
        $email = $request->getParameter('email');
        $password = $request->getParameter('password');

        if ( !( $email && $password ) ) {
            return $this->createJsonResponse([
                    'code' => ApiHttpStatus::BAD_REQUEST,
                    'message' => 'Validation failed',
                    'errors' => [
                        'children' => [
                            'email' => !$email ? ['errors' => ['Please provide an email']] : new ArrayObject,
                            'password' => !$password ? ['errors' => ['Please provide a password']] : new ArrayObject,
                        ],
                    ],
                ], ApiHttpStatus::BAD_REQUEST);
        }

        $query = $this->buildQuery($request, [
            'criteria' => [
                'password' => ['value' => $password, 'type' => 'equal'],
                'email' => ['value' => $email, 'type' => 'equal'],
            ],
        ]);

        $customerService = $this->getService('api_customers_service');

        if ( !$customerService->identify($query) ) {
            return $this->createJsonResponse([
                    'code' => ApiHttpStatus::UNAUTHORIZED,
                    'message' => 'Verification failed',
                ], ApiHttpStatus::UNAUTHORIZED);
        }
        
        return $this->createJsonResponse([
            'code' => ApiHttpStatus::SUCCESS,
            'message' => 'Verification successful',
            'success' => [
               'customer' => $customerService->getIdentifiedCustomer(),
            ],
        ]);
    }

    /**
     *
     * @param sfWebRequest $request
     * @return array
     * @TODO everything... maybe reusing the match-maker array
     */
    public function create(sfWebRequest $request)
    {
        $data = $request->getParameter('application/json');
        foreach ( ['lastName', 'email', 'password'] as $field ) {
            if ( !( isset($data[$field]) && $data[$field] ) ) {
                $data[$field] = ['errors' => 'Please enter your ' . $field];
                return $this->createJsonResponse([
                    'code' => ApiHttpStatus::BAD_REQUEST,
                    'message' => 'Validation failed',
                    'errors' => [
                        'children' => $data,
                    ],
                ], ApiHttpStatus::BAD_REQUEST);
            }
        }
        
        return parent::create($request);
    }

    /**
     *
     * @param sfWebRequest $request
     * @return array
     * @TODO everything... maybe reusing the match-maker array
     */
    public function update(sfWebRequest $request)
    {
        $data = $request->getParameter('application/json', []);
        $customers = $this->getService('api_customers_service');
        
        $contact = $customers->getIdentifiedContact();
        if ( $contact->id != $data['id'] ) {
            return $this->createJsonResponse([
                'code' => ApiHttpStatus::UNAUTHORIZED,
                'message' => 'You can only modify your own data',
            ], ApiHttpStatus::UNAUTHORIZED);
        }

        return $customers->update($data)
            ? $this->createJsonResponse($customers->getIdentifiedCustomer())
            : $this->createJsonResponse([
                'code' => ApiHttpStatus::INTERNAL_SERVER_ERROR,
                'message' => 'Internal Server Error',
            ], ApiHttpStatus::INTERNAL_SERVER_ERROR)
        ;
    }

    /**
     *
     * @param sfWebRequest $request
     * @return array
     */
    public function getOne(sfWebRequest $request)
    {
        $customers = $this->getService('api_customers_service');

        $contact = $customers->getIdentifiedContact();
        $result = !$pro instanceof Contact ? new ArrayObject : $customers->getFormattedEntity($contact);
        return $this->createJsonResponse($result);
    }

    /**
     *
     * @param sfWebRequest $request
     * @param array $query
     * @return array
     */
    public function getAll(sfWebRequest $request, array $query)
    {
        $customers = $this->getService('api_customers_service');

        if ( $customers->isIdentificated() && !$query['criteria'] ) {
            $customer = $customers->getIdentifiedCustomer();
            $query['criteria']['id']['value'] = $customer['id'];
            $query['criteria']['id']['type'] = 'equal';
            return $this->createJsonResponse($this->getListWithDecorator([$customer], $query));
        }

        // restricts access to customers collection to requests filtering on password and email
        if ( !$customers->isIdentificated() && !$query['criteria'] )
            return $this->createJsonResponse($this->getListWithDecorator([], $query));
        if ( !( isset($query['criteria']['email']) && isset($query['criteria']['password']) && isset($query['criteria']['email']['value']) && isset($query['criteria']['password']['value']) ) )
            return $this->createJsonResponse($this->getListWithDecorator([], $query));

        $customer = $customers->identify($query);

        return $this->createJsonResponse($this->getListWithDecorator([$customers->getIdentifiedCustomer()], $query));
    }
}
