<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\CancelCommandStrategy;

/**
 * Description of CancelCommandStrategyTest
 *
 * @author chris
 */
class CancelCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $slackApiMock;
    private $statusCommandMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->conquestRepositoryMock = $this->getMockBuilder(\dal\managers\ConquestRepository::class)
                ->setMethods(['GetCurrentConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->zoneRepositoryMock = $this->getMockBuilder(\dal\managers\ZoneRepository::class)
                ->setMethods(['GetZone', 'DeleteZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();

        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->statusCommandMock = $this->getMockBuilder(\framework\command\StatusCommandStrategy::class)
                ->setMethods(['Process', 'SendResponse'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->command = new CancelCommandStrategy($this->conquestRepositoryMock,
                $this->zoneRepositoryMock, $this->slackApiMock,
                $this->statusCommandMock);
    }

    public function testCancelZoneSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);

        $zone = new \dal\models\ZoneModel();
        $zone->is_owned = false;
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'cancel zone 1',
            'user' => 'TEST USER',
        );

        $this->zoneRepositoryMock->expects($this->once())
                ->method('DeleteZone');
        $this->statusCommandMock->expects($this->once())
                ->method('Process');

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testCancelZoneFail()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);

        $zone = new \dal\models\ZoneModel();
        $zone->is_owned = true;
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'cancel zone 1',
            'user' => 'TEST USER',
        );

        $this->zoneRepositoryMock->expects($this->never())
                ->method('DeleteZone');
        $this->statusCommandMock->expects($this->once())
                ->method('Process');

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

}
