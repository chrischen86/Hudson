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

    public function CreateBarChart($data)
    {
        if (sizeof($data) <= 0)
        {
            return "Please provide data to chart";
        }

        $queryString = 'cht=bvg'; //Line chart
        $queryString .= '&chtt=' . urlencode('Participation History'); //Title
        $queryString .= '&chs=700x250'; //Dimensions
        $queryString .= '&chds=a'; //Auto scale
        $queryString .= '&chbh=a'; //Auto scale bars
        $queryString .= '&chxs=1,000000,0,-1,_,FF0000'; //Hide y axis
        $queryString .= '&chma=30,30,20,20|120,30'; //Margins
        $queryString .= '&chco=F44336,03A9F4,4CAF50,FFC107'; //Bar colors
        $queryString .= '&chdl=' . urlencode('Total Participants|Phase 1|Phase 2|Phase 3');
        $queryString .= '&chm=N,000000,0,-1,10|N,000000,1,-1,10|N,000000,2,-1,10|N,000000,3,-1,10'; //Data labels

        $queryString .= '&chxt=x,y';
        $rows = array();
        for ($col = 0; $col < 4; $col++)
        {
            $dataPoint = array();
            foreach ($data as $bar)
            {
                array_push($dataPoint, $bar[$col]);
            }
            array_push($rows, implode(',', $dataPoint));
        }
        $queryString .= '&chd=t:' . implode('|', $rows);
        
        $keys = array_keys($data);
        $queryString .= '&chxl=0:|' . urlencode(implode('|', $keys));

        $uri = $this->GoogleChartApi . '?' . $queryString;
        return $uri;
    }

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
        $queryString .= '&chxs=1,000000,0,-1,_,FF0000'; //Hide y axis
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
            $chxl = $keys[0] . '|' . $keys[$length - 1];
        }
        else
        {
            $chxl = $keys[0] . '|' . $keys[$length / 2] . '|' . $keys[$length - 1];
        }
        $queryString .= '&chxl=0:|' . urlencode($chxl);

        $uri = $this->GoogleChartApi . '?' . $queryString;
        return $uri;
    }

}
