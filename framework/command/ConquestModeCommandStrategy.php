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
class ConquestModeCommandStrategy implements ICommandStrategy
{
    const Regex = '/(?:conquest mode) (lead|consensus)/i';

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
        return preg_match(ConquestModeCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $data = $payload['text'];
        
        $matches = [];
        if (preg_match(ConquestModeCommandStrategy::Regex, $data, $matches))
        {
            $state = $matches[1];
        }
        else
        {
            $this->response = "Could not modify consensus mode status.";
            return;
        }
        
        $this->coreRepository->SetState($state == 'consensus' ? StateEnum::Consensus : StateEnum::Coordinating);
        $this->response = "Conquest mode has been set to $state";
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);
        unset($this->response);
        unset($this->eventData);
    }
}
