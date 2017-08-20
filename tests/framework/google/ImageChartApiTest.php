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

}
