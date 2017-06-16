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
class osApiManifestationsActions extends apiActions
{
    public function getMyService()
    {
        return $this->getService('api_manifestations_service');
    }
    /**
     *
     * @param sfWebRequest $request
     * @param array $query
     * @return array (sfView::NONE)
     */
    public function getAll(sfWebRequest $request, array $query)
    {
        
        /* @var $manifService ApiManifestationsService */
        $manifService = $this->getService('api_manifestations_service');
        $result = $this->getListWithDecorator($manifService->findAll($query), $query);
        return $this->createJsonResponse($result);
    }
    /**
     *
     * @param sfWebRequest $request
     * @return array
     */
    public function getOne(sfWebRequest $request)
    {
        $manif_id = $request->getParameter('id', 0);
        
        /* @var $manifService ApiManifestationsService */
        $manifService = $this->getService('api_manifestations_service');

        $result = $manifService->findOneById($manif_id);
        return $this->createJsonResponse($result);
    }
    public function update(sfWebRequest $request)
    {
        $status = ApiHttpStatus::SUCCESS;
        $message = ApiHttpMessage::UPDATE_SUCCESSFUL;

        $manif_id = $request->getParameter('id', 0);
        
        /* @var $manifService ApiManifestationsService */
        $manifsService = $this->getService('api_manifestations_service');
        
        $isSuccess = $manifsService->update($manif_id, $request->getParameter('application/json'));

        if (!$isSuccess) {
            $status = ApiHttpStatus::BAD_REQUEST;
            $message = ApiHttpMessage::UPDATE_FAILED;
        }

        return $this->createJsonResponse([
                "code" => $status,
                'message' => $message
                ], $status);
    }
    public function delete(sfWebRequest $request)
    {
        $status = ApiHttpStatus::SUCCESS;
        $message = ApiHttpMessage::DELETE_SUCCESSFUL;

        $manif_id = $request->getParameter('id', 0);
        
        /* @var $manifService ApiManifestationsService */
        $manifService = $this->getService('api_manifestations_service');
        
        
        $isSuccess = $manifService->delete($manif_id);
        if (!$isSuccess) {
            $status = ApiHttpStatus::BAD_REQUEST;
            $message = ApiHttpMessage::DELETE_FAILED;
        }
        return $this->createJsonResponse([
                "code" => $status,
                'message' => $message
                ], $status);
    }
    
    public function create(sfWebRequest $request)
    {
        die('create');
    }
}
