<?php

namespace tests\framework\system;

use tests\TestCaseBase;

/**
 * Description of SlackFileManagerTest
 *
 * @author chris
 */
class DeleteFileCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $fileManagerMock;
    private $slackApiMock;

    protected function setUp()
    {
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->fileManagerMock = $this->getMockBuilder(\framework\system\SlackFileManager::class)
                ->setMethods(['SetState'])
                ->setConstructorArgs([$this->slackApiMock])
                ->getMock();



        $this->command = new \framework\system\DeleteFileCommandStrategy($this->fileManagerMock, $this->slackApiMock);
    }

    public function testTrainingodeOn()
    {
        $payload = array(
            'channel' => 'ADFAS',
            'text' => 'file delete oldest 20',
        );
        //$this->command->Process($payload);
        //$this->command->SendResponse();
    }

}
