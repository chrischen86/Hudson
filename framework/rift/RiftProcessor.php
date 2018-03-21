<?php

namespace framework\rift;

use framework\command\ICommandStrategy;
use dal\managers\RiftTypeRepository;
use dal\managers\UserRepository;
use framework\slack\ISlackApi;

/**
 * Description of RiftProcessor
 *
 * @author chris
 */
class RiftProcessor implements ICommandStrategy
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    private $slackApi;
    private $riftTypeRepository;
    private $response;
    private $attachments;
    private $channel;

    public function __construct(RiftTypeRepository $riftTypeRepository,
                                UserRepository $userRepository,
                                ISlackApi $slackApi)
    {
        $this->riftTypeRepository = $riftTypeRepository;
        $this->slackApi = $slackApi;
        $this->userRepository = $userRepository;
    }

    public function IsJarvisCommand()
    {
        return false;
    }

    public function IsSupportedRequest($text)
    {
        return false;
    }

    public function Process($payload)
    {
        $message = $payload['text'];
        $owner = $payload['user_id'];
        $riftType = $this->ProcessType($message);

        $user = $this->userRepository->GetUserById($owner);
        if ($user == null)
        {
            $this->response = "Unfortunatly I'm not sure who you are.  You are not registered in my database.";
            return;
        }
        
        $time = $this->ProcessTime($message);        
        $colour = $this->GetColour($user->vip);
        $attachments = array();
        array_push($attachments, array(
            'color' => $colour,
            'text' => '',
            'fields' => array(
                array(
                    'title' => 'Owner',
                    'value' => "<@" . $owner . ">",
                ),
                array(
                    'title' => 'Type',
                    'value' => $riftType->name,
                ),
                array(
                    'title' => 'Time',
                    'value' => $time,
                ),
            ),
            'thumb_url' => $riftType->thumbnail,
        ));

        $this->attachments = $attachments;
        $this->channel = $payload['channel_id'];
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, $this->attachments, $this->channel);
        unset($this->response);
        unset($this->attachments);
        unset($this->channel);
    }
    
    private function ProcessTime($message)
    {
        $type = explode(' ', $message)[0];
        $time = str_ireplace($type, '', $message);
        return $time;
    }

    private function ProcessType($message)
    {
        $type = explode(' ', $message)[0];
        return $this->riftTypeRepository->GetRiftType($type);
    }

    private function GetColour($vip)
    {
        switch ($vip)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                return RiftLevel::$Advanced;
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
                return RiftLevel::$Rare;
            case 10:
            case 11:
            case 12:
            case 13:
            case 14:
                return RiftLevel::$Heroic;
            case 15:
            case 16:
            case 17:
            case 18:
            case 19:
                return RiftLevel::$Legendary;
            case 20:
                return RiftLevel::$Mythic;
            default:
                return RiftLevel::$Normal;
        }
    }

}
