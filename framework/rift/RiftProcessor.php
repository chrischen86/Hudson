<?php

namespace framework\rift;

use framework\command\ICommandStrategy;
use dal\managers\RiftTypeRepository;
use framework\slack\ISlackApi;

/**
 * Description of RiftProcessor
 *
 * @author chris
 */
class RiftProcessor implements ICommandStrategy
{
    private $slackApi;
    private $riftTypeRepository;
    private $response;
    private $attachments;

    public function __construct(RiftTypeRepository $riftTypeRepository,
                                ISlackApi $slackApi)
    {
        $this->riftTypeRepository = $riftTypeRepository;
        $this->slackApi = $slackApi;
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
        $owner = $payload['user'];
        $riftType = $this->ProcessType($message);
        //$this->ProcessTime($message, $toReturn);
        //$this->ProcessThumbUri($toReturn);
    }

    public function SendResponse()
    {
        
    }

    private function ProcessTime(Request $message, MessageDto &$dto)
    {
        $text = $message->get("text");
        $time = str_ireplace($dto->riftKind, '', $text);
        $dto->time = $time;
//        if (preg_match($this->TimeRegex, $text, $matches))
//        {
//            $dto->time = $matches[0];
//        }
    }

    private function ProcessType($message)
    {
        
    }

}
