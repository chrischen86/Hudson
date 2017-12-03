<?php

namespace framework\system;

use framework\command\ICommandStrategy;
use framework\system\SlackFileManager;
use framework\slack\ISlackApi;
use DateTime;
/**
 * Description of DeleteFileCommandStrategy
 *
 * @author chris
 */
class DeleteFileCommandStrategy implements ICommandStrategy
{
    const Regex = '/(file delete oldest)(?:\s)(\d+)/i';

    private $eventData;
    private $fileManager;
    private $slackApi;
    private $response;
    private $attachments;

    public function __construct(SlackFileManager $fileManager,
                                ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;
        $this->fileManager = $fileManager;
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(DeleteFileCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $data = $payload['text'];
        $matches = [];
        if (!preg_match(DeleteFileCommandStrategy::Regex, $data, $matches))
        {
            return;
        }

        $amount = $matches[2];
        if ($amount > 1000)
        {
            $this->response = "To prevent systems locking up, I must insist file deletion be less than 1000 at a time.";
            return;
        }
        
        $dateTime = new DateTime();
        $dateTime->modify('-3 month');
        
        $warningMessage = "Attempting to delete *" . $amount . "* images.  This may take some time during which I will be unresponsive.";
        $this->slackApi->SendMessage($warningMessage, null, $this->eventData['channel']);
        
        $this->fileManager->DeleteOldImagesVerbose($dateTime, $amount, $this->eventData['channel']);        
        $this->response = "I have succesfully removed *" . $amount . "* files!  Pinned or starred items have not been affected.";
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, $this->attachments, $this->eventData['channel']);
        unset($this->response);
        unset($this->attachments);
        unset($this->eventData);
    }
}
