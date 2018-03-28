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
        $this->slackApiMock->expects($this->once())
             ->method('SendMessage')
             ->with($this->equalTo("*************** *Scheduled Rift* ***************"),
                    $this->callback(function($attachments) {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === 'Angel';
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'ND+1';
                        return $colorCorrect && $typeCorrect && $timeCorrect;
                    }));
        $this->command->Process($payload);        
        $this->command->SendResponse();        
    }

    public function testRiftCreateLongDateSuccess()
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
            'text' => 'Angel After New Day',
            'user_id' => 'Test User'
        );
        $this->slackApiMock->expects($this->once())
             ->method('SendMessage')
             ->with($this->equalTo("*************** *Scheduled Rift* ***************"),
                    $this->callback(function($attachments) {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === 'Angel';
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'After New Day';
                        return $colorCorrect && $typeCorrect && $timeCorrect;
                    }));
        $this->command->Process($payload);        
        $this->command->SendResponse();        
    }

    public function testRiftCreateNoTypeSuccess()
    {
        $rift = new \dal\models\RiftTypeModel();                  
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
            'text' => 'ND+1',
            'user_id' => 'Test User'
        );
        $this->slackApiMock->expects($this->once())
             ->method('SendMessage')
             ->with($this->equalTo("*************** *Scheduled Rift* ***************"),
                    $this->callback(function($attachments) {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === null;
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'ND+1';
                        return $colorCorrect && $typeCorrect && $timeCorrect;
                    }));
        $this->command->Process($payload);        
        $this->command->SendResponse();        
    }

    public function testRiftCreateFailure()
    {
        $rift = new \dal\models\RiftTypeModel();  
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

        $this->slackApiMock->expects($this->once())
             ->method('SendMessage')
             ->with($this->equalTo("*************** *Scheduled Rift* ***************"),
                    $this->callback(function($attachments) {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === null;
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'Nagel';
                        return $colorCorrect && $typeCorrect && $timeCorrect;
                    }));
        $this->command->Process($payload);   
        $this->command->SendResponse();        
    }
}