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
class osApiEventsActions extends apiActions {

    public function getMyService() {
        return $this->getService('api_events_service');
    }

    /**
     *
     * @param sfWebRequest $request
     * @param array $query
     * @return array (sfView::NONE)
     */
    public function getAll(sfWebRequest $request, array $query) {

        /* @var $eventService ApiEventsService */
        $eventService = $this->getService('api_events_service');
        $result = $this->getListWithDecorator($eventService->findAll($query), $query);
        return $this->createJsonResponse($result);
    }

    /**
     *
     * @param sfWebRequest $request
     * @return array
     */
    public function getOne(sfWebRequest $request) {
        $event_id = $request->getParameter('id', 0);

        /* @var $eventService ApiEventsService */
        $eventService = $this->getService('api_events_service');

        $result = $eventService->findOneById($event_id);
        return $this->createJsonResponse($result);
    }

    /**
     * Action for a POST|PUT:/[resource]/id request
     * The specified id has to be retrieved from the $request
     * The id key is defined in routing.yml
     *
     * @param sfWebRequest $request
     * @return string (sfView::NONE)
     */
    public function update(sfWebRequest $request) {
        $status = ApiHttpStatus::SUCCESS;
        $message = ApiHttpMessage::UPDATE_SUCCESSFUL;

        $event_id = $request->getParameter('id', 0);

        /* @var $eventService ApiEventsService */
        $eventsService = $this->getService('api_events_service');

        $isSuccess = $eventsService->updateEvent($event_id, $request->getParameter('application/json'));

        if (!$isSuccess) {
            $status = ApiHttpStatus::BAD_REQUEST;
            $message = ApiHttpMessage::UPDATE_FAILED;
        }

        return $this->createJsonResponse([
                    "code" => $status,
                    'message' => $message
                        ], $status);
    }

    public function delete(sfWebRequest $request) {
        
        $status = ApiHttpStatus::SUCCESS;
        $message = ApiHttpMessage::DELETE_SUCCESSFUL;

        $event_id = $request->getParameter('id', 0);
        
        /* @var $eventService ApiEventsService */
        $eventsService = $this->getService('api_events_service');
   
        $isSuccess = $eventsService->deleteEvent($event_id);
        if (!$isSuccess) {
            $status = ApiHttpStatus::BAD_REQUEST;
            $message = ApiHttpMessage::DELETE_FAILED;
        }
        return $this->createJsonResponse([
                "code" => $status,
                'message' => $message
                ], $status);
    }
    
    public function create(sfWebRequest $request) {
        
    }

}
