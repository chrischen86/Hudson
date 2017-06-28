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
    
    public function etstCreateClearStrategy()
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
}
