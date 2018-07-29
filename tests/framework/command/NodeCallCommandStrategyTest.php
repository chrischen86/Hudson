<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\NodeCallCommandStrategy;

/**
 * Description of NodeCallCommandStrategyTest
 *
 * @author chris
 */
class NodeCallCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $nodeRepositoryMock;
    private $strikeRepositoryMock;
    private $userRepositoryMock;
    private $slackApiMock;
    private $statusCommandMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->conquestRepositoryMock = $this->getMockBuilder(\dal\repositories\ConquestRepository::class)
                ->setMethods(['GetCurrentConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->zoneRepositoryMock = $this->getMockBuilder(\dal\repositories\ZoneRepository::class)
                ->setMethods(['GetZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->nodeRepositoryMock = $this->getMockBuilder(\dal\repositories\NodeRepository::class)
                ->setMethods(['GetMessageChannel', 'SetMessageProperties'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->strikeRepositoryMock = $this->getMockBuilder(\dal\repositories\StrikeRepository::class)
                ->setMethods(['GetStrikesByZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->userRepositoryMock = $this->getMockBuilder(\dal\repositories\UserRepository::class)
                ->setMethods(['GetUserById'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->statusCommandMock = $this->getMockBuilder(\framework\command\StatusCommandStrategy::class)
                ->setMethods(['Process', 'SendResponse'])
                ->disableOriginalConstructor()
                ->getMock();
        
        $this->command = new NodeCallCommandStrategy($this->conquestRepositoryMock,
                $this->zoneRepositoryMock, $this->nodeRepositoryMock,
                $this->strikeRepositoryMock, $this->userRepositoryMock,
                $this->slackApiMock, $this->statusCommandMock);
    }

    public function testNodeCallSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        $user = new \dal\models\UserModel();
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $zone = new \dal\models\ZoneModel();
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);
        
        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => '1.2',
            'user' => 'TEST USER',
            'ts' => 123.123123,
        );

        $this->statusCommandMock->expects($this->once())
                ->method('Process');
        
        $this->command->Process($payload);
        $this->command->SendResponse();        
    }
    
    public function testNodeCallTrailingSpacesSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        $user = new \dal\models\UserModel();
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $zone = new \dal\models\ZoneModel();
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);
        
        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => '1.2   ',
            'user' => 'TEST USER',
            'ts' => 123.123123,
        );

        $this->statusCommandMock->expects($this->once())
                ->method('Process');
        
        $this->command->Process($payload);
        $this->command->SendResponse();        
    }
    
    public function testNodeCallAssignSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        $user = new \dal\models\UserModel();
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $zone = new \dal\models\ZoneModel();
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);
        
        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => '1.2 <@UD1A3S>',
            'user' => 'TEST USER',
            'ts' => 123.123123,
        );

        $this->statusCommandMock->expects($this->once())
                ->method('Process');
        
        $this->command->Process($payload);
        $this->command->SendResponse();        
    }

    public function testNodeCallAssignTrailingSpacesSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        $user = new \dal\models\UserModel();
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $zone = new \dal\models\ZoneModel();
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);
        
        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => '1.2 <@UD1A3S>    ',
            'user' => 'TEST USER',
            'ts' => 123.123123,
        );

        $this->statusCommandMock->expects($this->once())
                ->method('Process');
        
        $this->command->Process($payload);
        $this->command->SendResponse();        
    }
}
