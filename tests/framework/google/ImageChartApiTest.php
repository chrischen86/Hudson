<?php

namespace tests\framework\google;

use tests\TestCaseBase;
use framework\google\ImageChartApi;

/**
 * Description of ImageChartApiTest
 *
 * @author chris
 */
class ImageChartApiTest extends TestCaseBase
{
    public function testChartApi()
    {
        $data = [];

        $chartApi = new ImageChartApi();
        $chartApi->CreateLineChart($data);
    }

    public function testBarChartApi()
    {
        $data = array(
            '2017/08/04' => array(15, 20, 25, 10),
            '2017/08/11' => array(20, 50, 50, 50),
            '2017/09/11' => array(20, 50, 50, 50),
            '2017/10/11' => array(20, 50, 50, 50),
            '2017/11/11' => array(20, 50, 50, 50)
        );

        $chartApi = new ImageChartApi();
        $uri = $chartApi->CreateBarChart($data);

        $this->assertEquals('https://chart.googleapis.com/chart?cht=bvg&chtt=Participation+History&chs=700x250&chds=a&chbh=a&chxs=1,000000,0,-1,_,FF0000&chma=30,30,20,20|120,30&chco=F44336,03A9F4,4CAF50,FFC107&chdl=Total+Participants%7CPhase+1%7CPhase+2%7CPhase+3&chm=N,000000,0,-1,10|N,000000,1,-1,10|N,000000,2,-1,10|N,000000,3,-1,10&chxt=x,y&chd=t:15,20,20,20,20|20,50,50,50,50|25,50,50,50,50|10,50,50,50,50&chxl=0:|2017%2F08%2F04%7C2017%2F08%2F11%7C2017%2F09%2F11%7C2017%2F10%2F11%7C2017%2F11%2F11', $uri);
    }

}
