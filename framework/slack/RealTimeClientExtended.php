<?php

namespace framework\slack;

use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Client\WebSocket;
use Slack\ConnectionException;
use Slack\RealTimeClient;
use Slack\Payload;
use DateTime;

class RealTimeClientExtended extends RealTimeClient
{
    public function connect()
    {
        $deferred = new Promise\Deferred();
        // Request a real-time connection...
        $this->apiCall('rtm.connect')
                // then connect to the socket...
                ->then(function (Payload $response)
                {
                    $responseData = $response->getData();
                    // Log PHPWS things to stderr
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter(new \Zend\Log\Writer\Stream('php://stderr'));
                    // initiate the websocket connection
                    $this->websocket = new WebSocket($responseData['url'], $this->loop, $logger);
                    $this->attachEvents($this->websocket);
                    return $this->websocket->open();
                }, function($exception) use ($deferred)
                {
                    // if connection was not succesfull
                    $deferred->reject(new ConnectionException(
                            'Could not connect to Slack API: ' . $exception->getMessage(), $exception->getCode()
                    ));
                })
                // then wait for the connection to be ready.
                ->then(function () use ($deferred)
                {
                    $this->once('hello', function () use ($deferred)
                    {
                        $deferred->resolve();
                    });
                    $this->once('error', function ($data) use ($deferred)
                    {
                        $deferred->reject(new ConnectionException(
                                'Could not connect to WebSocket: ' . $data['error']['msg'], $data['error']['code']));
                    });
                });
        return $deferred->promise();
    }

    protected function attachEvents($websocket)
    {
        $websocket->on('message', function ($message)
        {
            $this->onMessage($message);
        });
        $websocket->on('message', function($message)
        {
            $this->onPingMessage($message);
        });
    }

    public function ping()
    {
        if (!$this->connected)
        {
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

    protected function onPingMessage(WebSocketMessageInterface $message)
    {
        $payload = Payload::fromJson($message->getData());
        if (isset($payload['reply_to']))
        {
            if (isset($this->pendingMessages[$payload['reply_to']]))
            {
                $deferred = $this->pendingMessages[$payload['reply_to']];
                $deferred->resolve();
                unset($this->pendingMessages[$payload['reply_to']]);
            }
        }
    }

    protected function onMessage(WebSocketMessageInterface $message)
    {
        // parse the message and get the event name
        $payload = Payload::fromJson($message->getData());
        if (isset($payload['type']))
        {
            switch ($payload['type'])
            {
                case 'hello':
                    $this->connected = true;
                    break;
                case 'pong':
                    error_log('pong response');
                    error_log(print_r($payload, 1));
                    break;
            }
            // emit an event with the attached json
            $this->emit($payload['type'], [$payload]);
        }
        else
        {
            // If reply_to is set, then it is a server confirmation for a previously
            // sent message
            if (isset($payload['reply_to']))
            {
                if (isset($this->pendingMessages[$payload['reply_to']]))
                {
                    $deferred = $this->pendingMessages[$payload['reply_to']];
                    // Resolve or reject the promise that was waiting for the reply.
                    if (isset($payload['ok']) && $payload['ok'] === true)
                    {
                        $deferred->resolve();
                    }
                    else
                    {
                        $deferred->reject($payload['error']);
                    }
                    unset($this->pendingMessages[$payload['reply_to']]);
                }
            }
        }
    }

}
