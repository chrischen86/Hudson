<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework\command;

/**
 * Description of ClearCommandStrategy
 *
 * @author chris
 */
class ClearCommandStrategy implements ICommandStrategy
{
    private $Regex = '/(clear) (\d{1,2})(\.|-)(\d{1,2})/i';
    
    public function IsSupportedRequest($text)
    {
       return preg_match($this->Regex, $text); 
    }

    public function Process($payload)
    {
        print_r('clear command');
    }

    public function SendResponse()
    {
        
    }
}
