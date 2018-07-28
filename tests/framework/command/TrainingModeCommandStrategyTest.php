<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\TrainingModeCommandStrategy;

/**
 * Description of TrainingModeCommandStrategyTest
 *
 * @author chris
 */
class TrainingModeCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $coreRepositoryMock;
    private $slackApiMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->coreRepositoryMock = $this->getMockBuilder(\dal\repositories\CoreRepository::class)
                ->setMethods(['SetState'])
                ->setConstructorArgs([$adapter])
                ->getMock();

        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();

        $this->command = new TrainingModeCommandStrategy($this->coreRepositoryMock, $this->slackApiMock);
    }

    public function testTrainingodeOn()
    {
        $this->coreRepositoryMock->expects($this->once())
                ->method('SetState');

        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo('Training mode has been enabled'));

        $payload = array(
            'channel' => 'ADFAS',
            'text' => 'training mode on',
        );
        $this->command->Process($payload);
        $this->command->SendResponse();
    }
}
