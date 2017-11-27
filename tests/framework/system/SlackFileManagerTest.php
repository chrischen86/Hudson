<?php

namespace tests\framework\system;

use tests\TestCaseBase;
use framework\system\SlackFileManager;
use framework\slack\SlackApi;
use DateTime;

/**
 * Description of SlackFileManagerTest
 *
 * @author chris
 */
class SlackFileManagerTest extends TestCaseBase
{
    private $slackApi;
    private $manager;

    protected function setUp()
    {
        $this->slackApi = new SlackApi();
        $this->manager = new SlackFileManager($this->slackApi);
    }
/*
    public function testFetchFileList()
    {
        $fileList = $this->manager->GetFileList();
        var_dump($fileList);
        //echo $fileList->paging;
    }
    
    public function testFetchFileListBefore()
    {
        $dateTime = new DateTime();
        $dateTime->modify('-3 month');
        $this->manager->GetImagesListBefore($dateTime);
    }
 * 
 */
}
