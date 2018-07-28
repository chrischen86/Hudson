<?php
namespace dal\enums;

/**
 * Description of StatusEnum
 *
 * @author chris
 */
abstract class StateEnum {
    const Sleeping = 0;
    const Coordinating = 1;
    const Training = 2;
    const Consensus = 3;
}
