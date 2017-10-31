<?php

namespace framework\command;

use dal\managers\UserRepository;
use framework\slack\ISlackApi;

class ArchiveUserCommandStrategy implements ICommandStrategy
{
    const Regex = '/(?:archive) (.+)/i';

    private $eventData;
    private $userRepository;
    private $slackApi;
    private $response;

    public function __construct(UserRepository $userRepository,
                                ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;
        $this->userRepository = $userRepository;
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(ArchiveUserCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $matches = [];
        if (preg_match(ArchiveUserCommandStrategy::Regex, $payload['text'], $matches))
        {
            $userName = $matches[1];
        }
        else
        {
            $this->response = "Please double check your syntax.";
            return;
        }

        $user = $this->userRepository->GetUserByName($userName);
        if ($user == null)
        {
            $this->response = "That user (" . $userName . ") is not registered in the system.  I cannot archive them.";
            return;
        }

        $this->userRepository->ArchiveUser($user);
        $this->response = "*" . $userName . "* has been archived!";
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);
        unset($this->response);
        unset($this->eventData);
    }

}
