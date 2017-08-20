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
        $queryString .= '&chtt=' . urlencode('Participation History'); //Title
        $queryString .= '&chs=400x250'; //Dimensions
        $queryString .= '&chds=a'; //Auto scale
        $queryString .= '&chls=3'; //Line width
        $queryString .= '&chxs=1,000000,13,-1,_,FF0000'; //Hide y axis
        $queryString .= '&chma=30,30,20,20'; //Margins
        $queryString .= '&chco=2196F3'; //Line color
        $queryString .= '&chxt=x,y';
        $queryString .= '&chd=t:' . implode(',', $data);
        
        $keys = array_keys($data);
        $length = sizeof($keys);
        $chxl = "";
        if ($length == 1)
        {
            $chxl = $keys[0];
        }
        else if ($length == 2 || $length == 3)
        {
            $chxl = $keys[0] . '|' . $keys[$length-1];
        }
        else
        {
            $chxl = $keys[0] . '|' . $keys[$length/2] . '|' . $keys[$length-1];
        }
        $queryString .= '&chxl=0:|' . urlencode($chxl);
        
        $uri = $this->GoogleChartApi . '?' . $queryString;        
        return $uri;
    }
}
