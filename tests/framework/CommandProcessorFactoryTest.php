<?php

namespace tests\framework;

require_once __DIR__.'/../TestCaseBase.php';

use tests\TestCaseBase;
use framework\CommandStrategyFactory;
use Symfony\Component\HttpFoundation\Request;
/**
 * Description of CommandProcessorFactoryTest
 *
 * @author chris`
 */
class CommandProcessorFactoryTest extends TestCaseBase
{
    /** @var CommandStrategyFactory */
    private $factory;
    
    protected function setUp()
    {
        $this->factory = $this->container->get('CommandStrategyFactory');
    }
    
    public function testNoMatchingStrategy()
    {
        $requestMock = $this->getMockBuilder(Request::class)
                ->setMethods(['getContent'])
                ->getMock();
        $requestMock->expects($this->once())
                ->method('getContent')
                ->willReturn($this->BuildJarvisMessage('asdfsdafas'));
                
        $strategy = $this->factory->GetCommandStrategy($requestMock);
        $this->assertNull($strategy);
    }
    
    public function testCreateClearStrategy()
    {
        $requestMock = $this->getMockBuilder(Request::class)
                ->setMethods(['getContent'])
                ->getMock();
        $requestMock->expects($this->once())
                ->method('getContent')
                ->willReturn($this->BuildJarvisMessage('clear 2.9'));
                
        $strategy = $this->factory->GetCommandStrategy($requestMock);
        $this->assertInstanceOf(\framework\command\ClearCommandStrategy::class, $strategy);
    }
    
    public function testCreateInitiateProcessor()
    {
        $requestMock = $this->getMockBuilder(Request::class)
                ->setMethods(['getContent'])
                ->getMock();
        $requestMock->expects($this->once())
                ->method('getContent')
                ->willReturn($this->BuildJarvisMessage('init ASC'));

        $strategy = $this->factory->GetCommandStrategy($requestMock);
        $this->assertInstanceOf(\framework\command\InitCommandStrategy::class, $strategy);
    }
    
    public function testCreateStrikeProcessor()
    {
        $requestMock = $this->getMockBuilder(Request::class)
                ->setMethods(['getContent'])
                ->getMock();
        $requestMock->expects($this->once())
                ->method('getContent')
                ->willReturn($this->BuildJarvisMessage('setup zone 9'));

        $strategy = $this->factory->GetCommandStrategy($requestMock);
        $this->assertInstanceOf(\framework\command\StrikeCommandStrategy::class, $strategy);
    }
    
    public function testCreateStatusProcessor()
    {
        $requestMock = $this->getMockBuilder(Request::class)
                ->setMethods(['getContent'])
                ->getMock();
        $requestMock->expects($this->once())
                ->method('getContent')
                ->willReturn($this->BuildJarvisMessage('status'));

        $strategy = $this->factory->GetCommandStrategy($requestMock);
        $this->assertInstanceOf(\framework\command\StatusCommandStrategy::class, $strategy);
    }
}
