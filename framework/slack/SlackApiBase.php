<?php

namespace framework\slack;

abstract class SlackApiBase implements ISlackApi
{
    protected $PostMessageApiUri = 'https://slack.com/api/chat.postMessage';
    protected $PostEphemeralApiUri = 'https://slack.com/api/chat.postEphemeral';
    protected $UpdateMessageApiUri = 'https://slack.com/api/chat.update';
    protected $GroupHistoryApiUri = 'https://slack.com/api/channels.history';
    protected $TopicApiUri = 'https://slack.com/api/channels.setTopic';
    protected $CheckPresenceUri = 'https://slack.com/api/users.getPresence';
    protected $DeleteMessageApiUri = 'https://slack.com/api/chat.delete';
    protected $FileListApiUri = 'https://slack.com/api/files.list';
    protected $FileDeleteApiUri = 'https://slack.com/api/files.delete';
    protected $AddReactionsApiUri = 'https://slack.com/api/reactions.add';
    protected $OpenDMChannelUri = 'https://slack.com/api/im.open';
}
