<?php

namespace framework\google;

/**
 * Description of ImageChartApi
 *
 * @author chris
 */
class ImageChartApi
{
    private $GoogleChartApi = 'https://chart.googleapis.com/chart';
    
    public function CreateLineChart($data)
    {
        if (sizeof($data) <= 0)
        {
            return "Please provide data to chart";
        }
        
        $queryString = 'cht=lc'; //Line chart
        $queryString .= '&chtt=Participation History'; //Title
        $queryString .= '&chs=400x250'; //Dimensions
        $queryString .= '&chds=a'; //Auto scale
        $queryString .= '&chls=3'; //Line width
        $queryString .= '&chxs=1,FFFFFF,13,-1,_,FF0000'; //Hide y axis
        $queryString .= '&chma=30,30,20,20'; //Margins
        $queryString .= '&chco=2196F3'; //Line color
        $queryString .= '&chxt=x,y';
        $queryString .= '&';
        $uri = $this->GoogleChartApi . '?' . $queryString;        
        return $uri;
    }
}
