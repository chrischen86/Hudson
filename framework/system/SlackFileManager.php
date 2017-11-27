<?php

namespace framework\system;

use framework\slack\ISlackApi;
use framwork\system\models\FileListModel;
use framwork\system\models\FileInfoModel;
use framwork\system\models\PagingModel;
use DateTime;

/**
 * Description of SlackFileManager
 *
 * @author chris
 */
class SlackFileManager
{
    /** @var ISlackApi */
    private $slackApi;

    public function __construct(ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;
    }

    public function GetFileList()
    {
        $toReturn = new FileListModel();
        $response = $this->slackApi->GetFileList();
        
        $toReturn->files = $this->ParseFiles($response->body);
        $toReturn->paging = $this->ParsePaging($response->body);
        return $toReturn;
    }
    
    public function GetImagesListBefore(DateTime $dateTime)
    {
        $toReturn = new FileListModel();
        $response = $this->slackApi->GetFileList(null, 1, 0, $dateTime->getTimestamp(), 'images');
        
        $toReturn->files = $this->ParseFiles($response->body);
        $toReturn->paging = $this->ParsePaging($response->body);
        return $toReturn;
    }

    private function ParseFiles($body)
    {
        $toReturn = array();
        foreach ($body->files as $file)
        {
            $fileInfo = new FileInfoModel();
            $fileInfo->num_stars = isset($file->num_stars) ? $file->num_stars : 0;
            $fileInfo->pinned_to = isset($file->pinned_to) ? $file->pinned_to : [];
            if (sizeof($fileInfo->pinned_to) > 0 || $fileInfo->num_stars > 0)
            {
                continue;
            }
            
            $fileInfo->id = $file->id;
            $fileInfo->name = $file->name;
            $fileInfo->timestamp = $file->timestamp;
            $fileInfo->filetype = $file->filetype;
            $fileInfo->size = $file->size;
            $fileInfo->url_private = $file->url_private;
            $fileInfo->user = $file->user;            
            array_push($toReturn, $fileInfo);
        }
        return $toReturn;
    }
    
    private function ParsePaging($body)
    {
        $toReturn = new PagingModel();
        $toReturn->count = $body->paging->count;
        $toReturn->total = $body->paging->total;
        $toReturn->page = $body->paging->page;
        $toReturn->pages = $body->paging->pages;
        return $toReturn;
    }
}
