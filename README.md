# SafeSlackWebhookHandler

A non fatal version of the Monolog SlackWebhookHandler which does not throw on curl error, but uses error_log() as a final fallback.

## Installation

Install it with composer:

```bash
composer require patrickfischer/safeslackwebhookhandler
```

Update `config/logging.php` to use the handler like so:

```
use PatrickFischer\SafeSlackWebhookHandler;

return [
    // other configuration

    'channels' => [
        // other channels

        'safe_slack' => [
            'driver'  => 'monolog',
            'handler' => SafeSlackWebhookHandler::class,
            'with' => [
                'webhookUrl' => env('LOG_SLACK_WEBHOOK_URL'),
                'channel' => '#absolutebackup',
                'username' => env('LOG_SLACK_USER'),
                'useAttachment' => true,
                'iconEmoji' => env('LOG_SLACK_EMOJI', 'https://api.iconify.design/fluent-emoji-flat/alarm-clock.svg'),
                'useShortAttachment' => false,
                'includeContextAndExtra' => false,
                'level' => env('LOG_LEVEL_SLACK', 'error'),
                'excludeFields' => [],
                'retries' => 1,
                'timeout_ms' => 1000,
            ],
        ],

    ]
];
```



## License

MIT