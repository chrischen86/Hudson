<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework;
use dal\managers\ConquestRepository;
use dal\managers\UserRepository;
use framework\slack\SlackApi;
use Config;

/**
 * Description of LeadCommandProcessor
 *
 * @author chris
 */
class LeadCommandProcessor implements ICommandProcessor {
    private $eventData;
    private $conquestRepository;
    private $userRepository;
    private $slackApi;
    
    private $response;
    private $topic;

    public function __construct($data) {
        $this->eventData = $data;        
        $this->slackApi = new SlackApi();
        
        $this->conquestRepository = new ConquestRepository();
        $this->userRepository = new UserRepository();
    }
    
    public function Process() 
    {
        if ($this->eventData['channel'] != Config::$ConquestChannel)
        {
            return;
        }
        
        $user = $this->userRepository->GetUserById($this->eventData['user']);
        if ($user == null)
        {
            $this->response = "You are not registered with me, and therefore cannot lead!";
            return;
        }
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $this->conquestRepository->SetCommander($user);
        $this->response = '<@' . $user->name . '> has volunteered to lead, please follow their instructions!';
        
        $this->topic = 'Commander: <@' . $user->name . '> ' .
                'for battle phase *' . $conquest->phase . '* ' .
                'on *' . $conquest->date->format('Y-m-d') . '*';
    }

    public function SendResponse() 
    {
        if ($this->eventData['channel'] != Config::$ConquestChannel)
        {
            return;
        }
        
        $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);
        $this->slackApi->SetTopic($this->topic, $this->eventData['channel']);
    }
}