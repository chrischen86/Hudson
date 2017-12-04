<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace dal\models;

/**
 * Description of ZoneModel
 *
 * @author chris
 */
class ConsensusModel {
    public $id;
    public $conquest_id;
    public $conquest;
    public $zone;
    public $votes;
    public $vetoes;
    public $message_ts;
}
