<?php

namespace framework\process;

/**
 * Description of ProcessManager
 *
 * @author chris
 */
class ProcessManager
{
    public function GetRtmProcesses($directory)
    {
        exec('ps ahxwwo pid,command', $out);
        return $this->GetPids($directory, $out);        
    }

    private function GetPids($currentDirectory, $out)
    {
        $pids = array();
        foreach ($out as $item)
        {
            if (strpos($item, $currentDirectory . '/rtmClient.php') === false)
            {
                continue;
            }

            $matches = [];
            $re = '/(?:\s)?(\d+)(?:\s)/';
            if (preg_match($re, $item, $matches))
            {
                array_push($pids, $matches[1]);
            }
        }
        return $pids;
    }

}
