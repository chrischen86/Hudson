<?php

namespace framework\events;

abstract class EventProcessor
{
    abstract public function GetEventName();
    abstract public function Process($payload);
}
