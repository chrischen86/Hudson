<?php

namespace tests\framework\rift;

use tests\TestCaseBase;
use framework\rift\RiftProcessor;
use dal\models\RiftHistoryModel;
use Httpful\Response;

/**
 * Description of ClearCommandStrategyTest
 *
 * @author RobTheRed (rmmontague@gmail.com)
 */
class RiftProcessorTest extends TestCaseBase
{
    private $command;
    private $slackApiMock;
    private $userRepositoryMock;
    private $riftTypeRepositoryMock;
    private $riftHistoryRepositoryMock;
    private $slackMessageHistoryRepositoryMock;
    private $responseMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->responseMock = $this->getMockBuilder(Response::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage', 'SendEphemeral', 'DeleteMessage'])
                ->getMock();
        $this->riftTypeRepositoryMock = $this->getMockBuilder(\dal\managers\RiftTypeRepository::class)
                ->setMethods(['GetRiftType'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->riftHistoryRepositoryMock = $this->getMockBuilder(\dal\managers\RiftHistoryRepository::class)
                ->setMethods(['CreateRiftHistory', 'GetCancellableRiftsByUser', 'SetIsDeleteOnRiftHistory'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->slackMessageHistoryRepositoryMock = $this->getMockBuilder(\dal\managers\SlackMessageHistoryRepository::class)
                ->setMethods(['CreateSlackMessageHistory'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->userRepositoryMock = $this->getMockBuilder(\dal\managers\UserRepository::class)
                ->setMethods(['GetUserById'])
                ->setConstructorArgs([$adapter])
                ->getMock();

        $this->command = new RiftProcessor($this->riftTypeRepositoryMock, $this->riftHistoryRepositoryMock, $this->userRepositoryMock, $this->slackApiMock, $this->slackMessageHistoryRepositoryMock);
    }

    public function testRiftHistoryCreated()
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
                ->willReturn($this->responseMock);
        $this->riftHistoryRepositoryMock->expects($this->once())
                ->method('CreateRiftHistory')
                ->with($this->callback(function(RiftHistoryModel $history) use ($user, $rift)
                        {
                            return $history->owner_id === $user->id && $history->type_id === $rift->id;
                        }));
        $this->command->Process($payload);
        $this->command->SendResponse();
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
                ->with($this->equalTo("*************** *Scheduled Rift* ***************"), $this->callback(function($attachments)
                        {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === 'Angel';
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'ND+1';
                            return $colorCorrect && $typeCorrect && $timeCorrect;
                        }))
                ->willReturn($this->responseMock);

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
                ->with($this->equalTo("*************** *Scheduled Rift* ***************"), $this->callback(function($attachments)
                        {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === 'Angel';
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'After New Day';
                            return $colorCorrect && $typeCorrect && $timeCorrect;
                        }))
                ->willReturn($this->responseMock);

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
                ->with($this->equalTo("*************** *Scheduled Rift* ***************"), $this->callback(function($attachments)
                        {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === null;
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'ND+1';
                            return $colorCorrect && $typeCorrect && $timeCorrect;
                        }))
                ->willReturn($this->responseMock);
        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testRiftCreateTypeWithSpaceAntManSuccess()
    {
        $rift = new \dal\models\RiftTypeModel();
        $rift->name = 'Ant Man';
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
            'text' => 'Ant Man ND + 1',
            'user_id' => 'Test User'
        );
        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo("*************** *Scheduled Rift* ***************"), $this->callback(function($attachments)
                        {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === 'Ant Man';  //Ant Man Or Yellow Jacket
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'ND + 1';
                            return $colorCorrect && $typeCorrect && $timeCorrect;
                        }))
                ->willReturn($this->responseMock);

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testRiftCreateTypeWithSpaceGiantManSuccess()
    {
        $rift = new \dal\models\RiftTypeModel();
        $rift->name = 'Giant Man';
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
            'text' => 'Giant Man ND+1',
            'user_id' => 'Test User'
        );
        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo("*************** *Scheduled Rift* ***************"), $this->callback(function($attachments)
                        {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === 'Giant Man';  //Ant Man Or Yellow Jacket
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'ND+1';
                            return $colorCorrect && $typeCorrect && $timeCorrect;
                        }))
                ->willReturn($this->responseMock);

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testRiftCreateTypeWithSpaceYellowjacketSuccess()
    {
        $rift = new \dal\models\RiftTypeModel();
        $rift->name = 'Yellow Jacket';
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
            'text' => 'Yellow Jacket ND+1',
            'user_id' => 'Test User'
        );
        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo("*************** *Scheduled Rift* ***************"), $this->callback(function($attachments)
                        {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === 'Yellow Jacket';  //Ant Man Or Yellow Jacket
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'ND+1';
                            return $colorCorrect && $typeCorrect && $timeCorrect;
                        }))
                ->willReturn($this->responseMock);

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
                ->with($this->equalTo("*************** *Scheduled Rift* ***************"), $this->callback(function($attachments)
                        {
                            $colorCorrect = $attachments[0]['color'] === \framework\rift\RiftLevel::$Legendary;
                            $typeCorrect = $attachments[0]['fields'][1]['value'] === null;
                            $timeCorrect = $attachments[0]['fields'][2]['value'] === 'Nagel';
                            return $colorCorrect && $typeCorrect && $timeCorrect;
                        }))
                ->willReturn($this->responseMock);
        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testRiftCancelSuccess()
    {
        $user = new \dal\models\UserModel();
        $user->vip = 19;
        $user->id = 'Test User';
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $payload = array(
            'channel_id' => 'TESTCHANNEL',
            'text' => 'cancel',
            'user_id' => 'Test User'
        );

        $riftHistory = new RiftHistoryModel();
        $riftHistory->id = 'riftHistoryId1';
        $slackMessage = new \dal\models\SlackMessageModel();
        $slackMessage->id = 'slackMessageId1';
        $slackMessage->channel = 'slackMessageChannel';
        $slackMessage->ts = '12345.6789';
        $riftHistory->slack_message = $slackMessage;

        $this->riftHistoryRepositoryMock->expects($this->once())
                ->method('GetCancellableRiftsByUser')
                ->willReturn([$riftHistory]);
        
        $this->riftHistoryRepositoryMock->expects($this->once())
                ->method('SetIsDeleteOnRiftHistory')
                ->with($this->equalTo('riftHistoryId1'), $this->equalTo(true));
        
        $this->slackApiMock->expects($this->once())
                ->method('DeleteMessage')
                ->with($this->equalTo('12345.6789'), $this->equalTo('slackMessageChannel'));

        $this->slackApiMock->expects($this->once())
                ->method('SendEphemeral')
                ->with($this->equalTo("I've removed your scheduled rift!"));
        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testRiftCancelFailureNoRecords()
    {
        $user = new \dal\models\UserModel();
        $user->vip = 19;
        $user->id = 'Test User';
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $payload = array(
            'channel_id' => 'TESTCHANNEL',
            'text' => 'cancel',
            'user_id' => 'Test User'
        );

        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo("Unable to find rift to cancel."))
                ->willReturn($this->responseMock);

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

}
