<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\HoldCommandStrategy;

/**
 * Description of HoldCommandStrategyTest
 *
 * @author chris
 */
class HoldCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $nodeRepositoryMock;
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
                ->setMethods(['GetZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->nodeRepositoryMock = $this->getMockBuilder(\dal\managers\NodeRepository::class)
                ->setMethods(['GetNode', 'UpdateNode'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->statusCommandMock = $this->getMockBuilder(\framework\command\StatusCommandStrategy::class)
                ->setMethods(['Process', 'SendResponse'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->command = new HoldCommandStrategy($this->conquestRepositoryMock,
                $this->zoneRepositoryMock, $this->nodeRepositoryMock,
                $this->slackApiMock, $this->statusCommandMock);
    }

    public function testHoldCallSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        
        $zone = new \dal\models\ZoneModel();
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);
        
        $node = new \dal\models\NodeModel();
        $this->nodeRepositoryMock->expects($this->once())
                ->method('GetNode')
                ->willReturn($node);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'hold 1.2',
            'user' => 'TEST USER',
        );

        $this->nodeRepositoryMock->expects($this->once())
                ->method('UpdateNode')
                ->with($this->callback(function($n){
                    return $n->is_reserved;
                }));
        $this->statusCommandMock->expects($this->once())
                ->method('Process');

        $this->command->Process($payload);
        
        $this->assertTrue($node->is_reserved, "Node should be reserved");
        
        $this->command->SendResponse();
    }
    
    public function testHoldCallOffSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        
        $zone = new \dal\models\ZoneModel();
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);
        
        $node = new \dal\models\NodeModel();
        $this->nodeRepositoryMock->expects($this->once())
                ->method('GetNode')
                ->willReturn($node);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'hold 1.2 off',
            'user' => 'TEST USER',
        );

        $this->nodeRepositoryMock->expects($this->once())
                ->method('UpdateNode')
                ->with($node);
        $this->statusCommandMock->expects($this->once())
                ->method('Process');

        $this->command->Process($payload);
        
        $this->assertFalse($node->is_reserved, "Node should be released");
        
        $this->command->SendResponse();
    }

}
