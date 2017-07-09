<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\LeadCommandStrategy;

/**
 * Description of LeadCommandStrategyTest
 *
 * @author chris
 */
class LeadCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestRepositoryMock;
    private $userRepositoryMock;
    private $slackApiMock;
    private $channel;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->conquestRepositoryMock = $this->getMockBuilder(\dal\managers\ConquestRepository::class)
                ->setMethods(['GetCurrentConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->userRepositoryMock = $this->getMockBuilder(\dal\managers\UserRepository::class)
                ->setMethods(['GetUserById'])
                ->setConstructorArgs([$adapter])
                ->getMock();

        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage', 'SetTopic'])
                ->getMock();

        $this->channel = $this->container->get('ConquestChannel');
        $this->command = new LeadCommandStrategy($this->conquestRepositoryMock, $this->userRepositoryMock, $this->slackApiMock, $this->channel);
    }

    public function testLeadSuccess()
    {
        $user = new \dal\models\UserModel();
        $user->name = 'TEST USER';
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn($user);

        $conquest = new \dal\models\ConquestModel();
        $conquest->date = new \DateTime();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);

        $payload = array(
            'channel' => $this->channel,
            'text' => 'ill lead',
            'user' => $user->name,
        );
        
        $this->slackApiMock->expects($this->once())
                ->method('SetTopic');

        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo('<@' . $user->name . '> has volunteered to lead, please follow their instructions!'));

        $this->command->Process($payload);
        $this->command->SendResponse();
    }
    
    public function testLeadUserDoesNotExist()
    {
        $this->userRepositoryMock->expects($this->once())
                ->method('GetUserById')
                ->willReturn(null);
        
        $payload = array(
            'channel' => $this->channel,
            'text' => 'ill lead',
            'user' => 'TEST USER',
        );
        
        $this->slackApiMock->expects($this->once())
                ->method('SetTopic');

        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo("You are not registered with me, and therefore cannot lead!"));

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

}
