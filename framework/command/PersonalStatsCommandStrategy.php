<?php

namespace framework\command;

use framework\conquest\ConquestManager;
use dal\managers\UserRepository;
use framework\slack\ISlackApi;
use DateTime;
/**
 * Description of LeadCommandStrategy
 *
 * @author chris
 */
class PersonalStatsCommandStrategy implements ICommandStrategy
{
    const Regex = '/(my stats)/i';

    private $channel;
    private $conquestManager;
    private $userRepository;
    private $slackApi;
    private $response;
    private $topic;

    public function __construct(ConquestManager $conquestManager,
                                UserRepository $userRepository,
                                ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;
        $this->conquestManager = $conquestManager;
        $this->userRepository = $userRepository;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(PersonalStatsCommandStrategy::Regex, $text);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function Process($payload)
    {
        $this->channel = $payload['channel'];
        $user = $this->userRepository->GetUserById($payload['user']);
        if ($user == null)
        {
            $this->response = "You are not registered with me, and therefore do not have any stats!";
            return;
        }

        $now = new DateTime();
        $count = $this->conquestManager->GetPersonalStats($now, $user);

        $response = "Calculating your personal stats...\n\n";

        if ($count >= 30)
        {
            $response .= "Congratulations <@" . $user->name . ">!  You have defeated and claimed *"
                    . $count . "* nodes for the glory of the alliance!";
        }
        else if ($count >= 20)
        {
            $response .= "Amazing job <@" . $user->name . ">!  You have defeated and claimed *"
                    . $count . "* nodes for the glory of the alliance!  _You need *" . (30 - $count)
                    . "* more hits for a mythic reward._";
        }
        else if ($count >= 10)
        {
            $response .= "Great work <@" . $user->name . ">!  You have defeated and claimed *"
                    . $count . "* nodes for the glory of the alliance!  _You need *" . (30 - $count)
                    . "* more hits for a mythic reward.  Keep at it!_";
        }
        else if ($count > 0)
        {
            $response .= "Good effort <@" . $user->name . ">!  You have defeated and claimed *"
                    . $count . "* nodes for the glory of the alliance!  _You need *" . (30 - $count)
                    . "* more hits for a mythic reward.  Don't be a slacker now :)_";
        }
        else
        {
            $response .= "<@" . $user->name . ">... You don't have any hits. _jarvis is sad_";
        }
        $this->response = $response;
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, null, $this->channel);
        unset($this->response);
        unset($this->channel);
    }

}
