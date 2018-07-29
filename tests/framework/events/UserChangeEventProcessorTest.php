<?php

namespace tests\framework\rift;

use tests\TestCaseBase;
use framework\events\UserChangeEventProcessor;
use dal\models\UserModel;
use Httpful\Response;

/**
 * Description of ClearCommandStrategyTest
 *
 * @author RobTheRed (rmmontague@gmail.com)
 */
class UserChangeEventProcessorTest extends TestCaseBase
{
    private $command;
    private $slackApiMock;
    private $userRepositoryMock;
    private $responseMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->responseMock = $this->getMockBuilder(Response::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['OpenDMChannel'])
                ->getMock();

        $this->userRepositoryMock = $this->getMockBuilder(\dal\repositories\UserRepository::class)
                ->setMethods(['GetUserById', 'UpdateUser'])
                ->setConstructorArgs([$adapter])
                ->getMock();

        $this->command = new UserChangeEventProcessor($this->userRepositoryMock, $this->slackApiMock);
    }

    public function testNameChangeSuccess()
    {
        //ARRANGE
        $user = new UserModel();
        $user->vip = 19;
        $user->id = 'U123';
        $user->name = 'display user 12345';
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $payload = array(
            'type' => 'user_change',
            'user' => array(
                'id' => 'U123',
                'name' => 'user 123',
                'real_name' => 'User 123',
                'profile' => array(
                    'title' => 'mock data provider',
                    'display_name' => 'display user 123',
                ),
            ),
        );

        $openDMChannelPayload = (object) array(
                    'ok' => true,
                    'no_op' => true,
                    'already_open' => true,
                    'channel' => (object) array(
                        'id' => 'D123',
                    ),
        );
        $this->responseMock->body = $openDMChannelPayload;

        $this->slackApiMock->expects($this->once())
                ->method('OpenDMChannel')
                ->willReturn($this->responseMock);
        $this->userRepositoryMock->expects($this->once())
                ->method('UpdateUser');

        //ACT
        $message = $this->command->Process($payload);

        //ASSERT
        $this->assertEquals("I noticed that you changed your display name!  I'll call you *display user 123* from now on.", $message->message, 'The message was not correct');
        $this->assertEquals('D123', $message->channel, 'The channel was not the direct message channel');
    }

    public function testProfileChangedButNotName()
    {
        //ARRANGE
        $user = new UserModel();
        $user->vip = 19;
        $user->id = 'U123';
        $user->name = 'display user 12345';
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);
        $payload = array(
            'type' => 'user_change',
            'user' => array(
                'id' => 'U123',
                'name' => 'user 123',
                'real_name' => 'User 123',
                'profile' => array(
                    'title' => 'mock data provider',
                    'display_name' => 'display user 12345',
                ),
            ),
        );

        $this->slackApiMock->expects($this->never())
                ->method('OpenDMChannel');

        //ACT
        $message = $this->command->Process($payload);

        //ASSERT
        $this->assertEquals(null, $message, 'No change was made to display name');
    }

}
