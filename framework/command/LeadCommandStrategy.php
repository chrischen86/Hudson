<?php

namespace framework\command;

use dal\managers\ConquestRepository;
use dal\managers\UserRepository;
use framework\slack\ISlackApi;

/**
 * Description of LeadCommandStrategy
 *
 * @author chris
 */
class LeadCommandStrategy implements ICommandStrategy
{
    const Regex = '/(command|take control|lead)/i';

    private $channel;
    private $conquestChannel;
    private $conquestRepository;
    private $userRepository;
    private $slackApi;
    private $response;
    private $topic;

    public function __construct(ConquestRepository $conquestRepository,
                                UserRepository $userRepository,
                                ISlackApi $slackApi, $conquestChannel)
    {
        $this->slackApi = $slackApi;
        $this->conquestRepository = $conquestRepository;
        $this->userRepository = $userRepository;
        
        $this->conquestChannel = $conquestChannel;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(LeadCommandStrategy::Regex, $text);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function Process($payload)
    {
        $this->channel = $payload['channel'];
        if ($this->channel != $this->conquestChannel)
        {
            $this->response = "Please use this command only in the conquest channel!";
            return;
        }

        $user = $this->userRepository->GetUserById($payload['user']);
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
        $this->slackApi->SendMessage($this->response, null, $this->channel);
        
        if ($this->channel != $this->conquestChannel)
        {
            return;
        }
        $this->slackApi->SetTopic($this->topic, $this->channel);
        
        unset($this->response);
        unset($this->topic);
        unset($this->channel);
    }

}
