<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework\command;
use dal\managers\CoreRepository;
use framework\slack\ISlackApi;
use StateEnum;
/**
 * Description of InitCommandStategy
 *
 * @author chris
 */
class InitCommandStrategy implements ICommandStrategy
{
    private $InitiateRegex = '/(initiate|init|begin) (ASC)/i';

    private $coreRepository;
    private $slackApi;
    private $response;

    public function __construct(CoreRepository $coreRepository, ISlackApi $slackApi)
    {
        $this->coreRepository = $coreRepository;
        $this->slackApi = $slackApi;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match($this->InitiateRegex, $text);
    }

    public function Process($payload)
    {
        error_log('icp: ' . $payload['text']);

        $stateModel = $this->coreRepository->GetState();

        if ($stateModel->state == StateEnum::Sleeping)
        {
            $this->response = "Activating Advanced Strike Coordination Mode";
            $this->coreRepository->SetState(StateEnum::Coordinating);
        }
        else
        {
            $this->response = "I am already assisting with the active conquest!";
        }
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response);
        unset($this->response);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

}
