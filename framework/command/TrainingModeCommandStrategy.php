<?php

namespace framework\command;
use dal\managers\CoreRepository;
use StateEnum;
use framework\slack\ISlackApi;
/**
 * Description of TrainingModeCommandStrategy
 *
 * @author chris
 */
class TrainingModeCommandStrategy implements ICommandStrategy
{
    const Regex = '/(?:training mode) (on|off)/i';

    private $eventData;
    private $coreRepository;
 
    private $slackApi;
    private $response;


    public function __construct(CoreRepository $coreRepository,
                                ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;
        $this->coreRepository = $coreRepository;      
    }
    
    public function IsJarvisCommand()
    {
        return true;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(TrainingModeCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $data = $payload['text'];
        
        $matches = [];
        if (preg_match(TrainingModeCommandStrategy::Regex, $data, $matches))
        {
            $state = $matches[1];
        }
        else
        {
            $this->response = "Could not modify training mode status.";
            return;
        }
        
        $this->coreRepository->SetState($state == 'on' ? StateEnum::Training : StateEnum::Coordinating);
        $stateString = $state == 'on' ? "enabled" : "disabled";
        $this->response = "Training mode has been $stateString";
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);
    }
}
