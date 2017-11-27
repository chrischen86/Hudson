<?php

namespace framework\system;

use framework\command\ICommandStrategy;
use framework\system\SlackFileManager;
use framework\slack\ISlackApi;
use DateTime;

class FileListCommandStrategy implements ICommandStrategy
{
    const Regex = '/(file) (list)/i';

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
        return preg_match(FileListCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;

        $dateTime = new DateTime();
        $dateTime->modify('-3 month');
        $fileList = $this->fileManager->GetImagesListBefore($dateTime);

        $fields = array();
        $message = "Slack contains " . $fileList->paging->total . " images older than *" . $dateTime->format('Y/m/d') . "*. "
                . "I have not counted any pinned or starred items.\n";

        $size = 0;
        foreach ($fileList->files as $file)
        {
            $size = $size + $file->size;
        }

        $message .= "This represents a total of *" . $this->FormatBytes($size) . "*";

        array_push($fields, array(
            'title' => 'File Summary',
            'value' => $message,
        ));

        $this->attachments = array();
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

    private function FormatBytes($value, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($value, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

}
