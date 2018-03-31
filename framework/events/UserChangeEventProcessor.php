<?php

namespace framework\events;

use dal\managers\UserRepository;
use framework\slack\ISlackApi;
use framework\slack\SlackMessage;

class UserChangeEventProcessor extends EventProcessor
{
    /**
     * @var ISlackApi
     */
    private $slackApi;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository,
                                ISlackApi $slackApi)
    {
        $this->userRepository = $userRepository;
        $this->slackApi = $slackApi;
    }

    public function GetEventName()
    {
        return 'user_change';
    }

    public function Process($payload)
    {
        $data = $payload['user'];
        $userId = $data['id'];
        $displayName = $data['profile']['display_name'];

        $user = $this->userRepository->GetUserById($userId);
        if ($user->name === $displayName)
        {
            return null;
        }
        
        $channel = $this->GetDirectMessageChannel($user->id);
        $slackMessage = new SlackMessage();
        $slackMessage->channel = $channel;
        if ($user == null)
        {
            $slackMessage->message = "You've updated your user profile but unfortunatly I'm not sure who you are. " . 
                    " You are not registered in my database. Please contact an administrator to set you up!";
            return $slackMessage;
        }
        
        $user->name = $displayName;
        $this->userRepository->UpdateUser($user);
        $slackMessage->message = "I noticed that you changed your display name!  I'll call you *$displayName* from now on.";
        return $slackMessage;
    }

    private function GetDirectMessageChannel($userId)
    {
        $response = $this->slackApi->OpenDMChannel($userId);
        var_dump($response);
        return $response->body->channel->id;
    }

}
