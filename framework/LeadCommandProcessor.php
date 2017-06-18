<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework;
use dal\managers\ConquestRepository;
use framework\slack\SlackApi;

/**
 * Description of LeadCommandProcessor
 *
 * @author chris
 */
class LeadCommandProcessor implements ICommandProcessor {
    private $eventData;
    private $conquestRepository;
    private $slackApi;
    
    private $response;

    public function __construct($data) {
        $this->eventData = $data;        
        $this->slackApi = new SlackApi();
        
        $this->conquestRepository = new ConquestRepository();
    }
    
    public function Process() 
    {        
        $user = $this->userRepository->GetUserById($this->eventData['user']);
        if ($user == null)
        {
            $this->response = "You are not registered with me, and therefore cannot lead!";
            return;
        }
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $this->conquestRepository->SetCommander($user);
        $this->response = '<@' . $user->name . '> has volunteered to lead, please follow their instructions!';
    }

    public function SendResponse() 
    {
        $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);
    }
}