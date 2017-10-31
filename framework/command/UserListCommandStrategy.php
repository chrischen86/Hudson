<?php

namespace framework\command;

use dal\managers\UserRepository;
use framework\slack\ISlackApi;

class UserListCommandStrategy implements ICommandStrategy
{
    const Regex = '/(user) (list)/i';

    private $eventData;
    private $userRepository;
    private $slackApi;
    private $response;
    private $attachments;

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
        return preg_match(UserListCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $users = $this->userRepository->GetActiveUsers();

        $fields = array();
        $message = "We currently have " . sizeof($users) . " active users registered with me.\n";
        
        foreach ($users as $user)
        {
            $message .= $user->name . "\n";
        }
        
        array_push($fields, array(
            'title' => 'User Summary',
            'value' => $message,
        ));

        array_push($this->attachments, array(
            'color' => "#FDC528",
            'text' => '',
            'fields' => $fields,
            'mrkdwn_in' => ["fields"]
        ));
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, $this->attachments, $this->eventData['channel']);
        unset($this->response);
        unset($this->attachments);
        unset($this->eventData);
    }

}
