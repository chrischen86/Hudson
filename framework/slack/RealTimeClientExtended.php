<?php

namespace framework\slack;
use Slack\RealTimeClient;
use DateTime;

class RealTimeClientExtended extends RealTimeClient
{
    public function Ping()
    {
        if (!$this->connected) {
            return \React\Promise\reject(new ConnectionException('Client not connected. Did you forget to call `connect()`?'));
        }
        $now = new DateTime();
        $data = [
            'id' => ++$this->lastMessageId,
            'type' => 'ping',
            'time' => $now->format('Y-m-d H:i:s'),
        ];
        $this->websocket->send(json_encode($data));
        // Create a deferred object and add message to pending list so when a
        // success message arrives, we can de-queue it and resolve the promise.
        $deferred = new \React\Promise\Deferred();
        $this->pendingMessages[$this->lastMessageId] = $deferred;
        return $deferred->promise();
    }
}
