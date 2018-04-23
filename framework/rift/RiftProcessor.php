<?php

namespace framework\rift;

use framework\command\ICommandStrategy;
use dal\managers\RiftTypeRepository;
use dal\managers\RiftHistoryRepository;
use dal\managers\UserRepository;
use framework\slack\ISlackApi;
use framework\system\SlackMessageHistoryHelper;
use dal\managers\SlackMessageHistoryRepository;

/**
 * Description of RiftProcessor
 *
 * @author chris
 */
class RiftProcessor implements ICommandStrategy
{
    /**
     * @var SlackMessageHistoryRepository
     */
    private $slackMessageHistoryRepository;

    /**
     * @var RiftHistoryRepository
     */
    private $riftHistoryRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;
    private $slackApi;
    private $riftTypeRepository;
    private $response;
    private $attachments;
    private $channel;
    /** @var RiftHistoryModel */
    private $history;

    public function __construct(RiftTypeRepository $riftTypeRepository,
                                RiftHistoryRepository $riftHistoryRepository,
                                UserRepository $userRepository,
                                ISlackApi $slackApi,
                                SlackMessageHistoryRepository $slackMessageHistoryRepository)
    {
        $this->riftTypeRepository = $riftTypeRepository;
        $this->slackApi = $slackApi;
        $this->userRepository = $userRepository;
        $this->riftHistoryRepository = $riftHistoryRepository;
        $this->slackMessageHistoryRepository = $slackMessageHistoryRepository;
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
        if($riftType === "cancel"){
            CancelRift($user);            
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
                    'value' => $user->name,
                    'short' => true,
                ),
                array(
                    'title' => 'Type',
                    'value' => $riftType->name,
                    'short' => true,
                ),
                array(
                    'title' => 'Time',
                    'value' => $time,
                    'short' => false
                ),
            ),
            'thumb_url' => $riftType->thumbnail,
        ));

        $this->response = "*************** *Scheduled Rift* ***************";
        $this->attachments = $attachments;
        $this->channel = $payload['channel_id'];

        $this->history = new \dal\models\RiftHistoryModel();
        $this->history->owner_id = $user->id;
        $this->history->type_id = $riftType != null ? $riftType->id : null;
        $this->history->scheduled_time = new \DateTime();
    }

    public function SendResponse()
    {
        $response = $this->slackApi->SendMessage($this->response, $this->attachments, $this->channel);
        $slackMessage = SlackMessageHistoryHelper::ParseResponse($response);
        
        $messageId = $this->slackMessageHistoryRepository->CreateSlackMessageHistory($slackMessage);
        $this->history->slack_message_id = $messageId;
        $this->riftHistoryRepository->CreateRiftHistory($this->history);
        
        unset($this->response);
        unset($this->attachments);
        unset($this->channel);
        unset($this->history);
    }

    private function ProcessTime($message)
    {
        $explodedMessage = explode(' ', $message);
        //if there aren't more than 1 parameters, the time is the first value.
        if (count($explodedMessage) === 1)
        {
            return $explodedMessage[0];
        }
        $type = $explodedMessage[0];
        //This if statement fixes the bug for Yellow Jacket, Giant Man or Ant Man rifts
        if(count($explodedMessage) >= 3 &&  
            (strtolower($explodedMessage[1]) === "man" ||
            strtolower($explodedMessage[1]) === "jacket")){
                $type = $explodedMessage[0] . " " . $explodedMessage[1];
        }
        
        
        $time = trim(str_ireplace($type, '', $message));
        return $time;
    }

    private function ProcessType($message)
    {
        $type = explode(' ', $message)[0];
        //Return if cancel, and then do the cancel processing above
        if(strtolower($type) === "cancel")
        {
            return "cancel";
        }
        return $this->riftTypeRepository->GetRiftType($type);
    }

    private function CancelRift($user){
        //Get rifts able to be cancelled by user id.
        $riftHistory = $this->riftHistoryRepostory->GetCancellableRiftsByUser($user);
        //if there aren't any available to cancel, alert user and exit.
        if($riftHistory == null || 
            count($riftHistory) <= 0){
            //TODO: send message "Unable to find rift to cancel."
            return;
        }

        //Cancel first rift in list (newest one in list as they're ordered desc)
        $riftToCancel = $riftHistory[0];
        $this->riftHistoryRepository->SetIsDeleteOnRiftHistory($riftHistory->$id, true);
        
        //then go to Slack Message history and delete last rift message for the current user.
        //TODO: For right now we'll not delete the message to avoid foreign key issues
        //$this->slackMessageHistoryRepository->DeleteSlackMessageHistoryRecord($riftToCancel->$slack_message_id);               

        //TODO: Alert user that the rift was successfully cancelled?
        //"Rift Cancelled."
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