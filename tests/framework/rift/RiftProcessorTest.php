<?php

namespace tests\framework\rift;

use tests\TestCaseBase;
use framework\rift\RiftProcessor;

/**
 * Description of ClearCommandStrategyTest
 *
 * @author RobTheRed (rmmontague@gmail.com)
 */
class RiftProcessorTest extends TestCaseBase
{
    private $command;
    private $slackApiMock;
    private $statusCommandMock;
    private $userRepositoryMock;
    private $riftTypeRepositoryMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();        
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->riftTypeRepositoryMock = $this->getMockBuilder(\dal\managers\RiftTypeRepository::class)
                ->setMethods(['GetRiftType'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->userRepositoryMock = $this->getMockBuilder(\dal\managers\UserRepository::class)
                ->setMethods(['GetUserById'])
                ->setConstructorArgs([$adapter])
                ->getMock();

        $this->command = new RiftProcessor($this->riftTypeRepositoryMock,
                $this->userRepositoryMock,
                $this->slackApiMock);
    }

    public function testRiftCreateSuccess()
    {
        $rift = new \dal\models\RiftTypeModel();  
        $rift->name = 'Angel';          
        $user = new \dal\models\UserModel();
        $user->vip = 19;
        $user->id = 'Test User';   
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $this->riftTypeRepositoryMock->expects($this->once())
                ->method('GetRiftType')
                ->willReturn($rift);
        $payload = array(            
            'channel_id' => 'TESTCHANNEL',
            'text' => 'Angel ND+1',
            'user_id' => 'Test User'
        );

        $this->command->Process($payload);        
        //TODO: test the output of the process function.
        //$this->assertEqual($this->attachments['color'], RiftLevel::$Legendary);
        //$this->assertEqual($this->attachments['fields'][1].value, 'Angel');
        //$this->assertEqual($this->attachments['fields'][2].value, 'ND+1');
        //assert type is angel, time is ND+1, and the owner is 'Test User'
        //Also figure out what color the test user should be.
        $this->command->SendResponse();
        
    }

    public function testRiftCreateFailure()
    {
        $rift = new \dal\models\RiftTypeModel();  
        $rift->name = 'Angel';          
        $user = new \dal\models\UserModel();
        $user->vip = 19;
        $user->id = 'Test User';   
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $this->riftTypeRepositoryMock->expects($this->once())
                ->method('GetRiftType')
                ->willReturn($rift);
        $payload = array(            
            'channel_id' => 'TESTCHANNEL',
            'text' => 'Nagel',
            'user_id' => 'Test User'
        );

        $this->command->Process($payload);
        $this->command->SendResponse();        
    }
}