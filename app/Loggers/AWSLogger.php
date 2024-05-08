<?php

namespace App\Loggers;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Carbon\Carbon;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Formatter\JsonFormatter;

class AWSLogger extends SystemLogger
{
    protected static function getStreamHandler()
    {
        $client = new CloudWatchLogsClient([
            'region' => env('AWS_REGION'),
            'version' => 'latest'
        ]);
        $groupName = env('CLOUDWATCH_LOG_GROUP_NAME', '');
        $streamName = sprintf(
            '%s/%s/%s',
            env('APP_ENV'),
            env('CLOUDWATCH_LOG_STREAM_NAME_PREFIX', ''),
            Carbon::now()->format('mdY')
        );
        $handler = new CloudWatch($client, $groupName, $streamName);
        $handler->setFormatter(new JsonFormatter());

        return $handler;
    }
}
