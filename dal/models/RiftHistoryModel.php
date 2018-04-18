<?php

namespace dal\models;

class RiftHistoryModel
{
    public $id;

    /** @var UserModel */
    public $owner;
    public $owner_id;

    /** @var RiftTypeModel */
    public $type;
    public $type_id;
    public $scheduled_time;

    /** @var SlackMessageModel */
    public $slack_message;
    public $slack_message_id;

    public $is_deleted;
}
