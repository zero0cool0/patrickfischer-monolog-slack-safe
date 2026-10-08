<?php

namespace PatrickFischer\Monolog;

use Monolog\Handler\SlackWebhookHandler;
use Monolog\Utils;
use Monolog\Handler\Slack\SlackRecord;
use Monolog\Level;
use Monolog\Handler\MissingExtensionException;
use Monolog\LogRecord;

class SafeSlackWebhookHandler extends SlackWebhookHandler
{
    /**
     * Slack Webhook token
     *
     * @var non-empty-string
     */
    private string $webhookUrl;

    /**
     * Instance of the SlackRecord util class preparing data for Slack API.
     */
    private SlackRecord $slackRecord;

    /**
     * @param non-empty-string $webhookUrl             Slack Webhook URL
     * @param string|null $channel                Slack channel (encoded ID or name)
     * @param string|null $username               Name of a bot
     * @param bool        $useAttachment          Whether the message should be added to Slack as attachment (plain text otherwise)
     * @param string|null $iconEmoji              The emoji name to use (or null)
     * @param bool        $useShortAttachment     Whether the the context/extra messages added to Slack as attachments are in a short style
     * @param bool        $includeContextAndExtra Whether the attachment should include context and extra data
     * @param string[]    $excludeFields          Dot separated list of fields to exclude from slack message. E.g. ['context.field1', 'extra.field2']
     *
     * @throws MissingExtensionException If the curl extension is missing
     */
    public function __construct(
        string $webhookUrl,
        ?string $channel = null,
        ?string $username = null,
        bool $useAttachment = true,
        ?string $iconEmoji = null,
        bool $useShortAttachment = false,
        bool $includeContextAndExtra = false,
        $level = Level::Critical,
        bool $bubble = true,
        array $excludeFields = [],
        private int $retries = 2,
        private int $timeout_ms = 1500,
    ) {
        if (!\extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the SlackWebhookHandler');
        }

        parent::__construct($webhookUrl, $channel, $username, $useAttachment, $iconEmoji, $useShortAttachment, $includeContextAndExtra, $level, $bubble, $excludeFields);

        $this->webhookUrl = $webhookUrl;

        $this->slackRecord = new SlackRecord(
            $channel,
            $username,
            $useAttachment,
            $iconEmoji,
            $useShortAttachment,
            $includeContextAndExtra,
            $excludeFields
        );
    }

    /**
     * @inheritDoc
     */
    protected function write(LogRecord $record): void
    {
        $postData = $this->slackRecord->getSlackData($record);
        $postString = Utils::jsonEncode($postData);

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->webhookUrl,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-type: application/json'],
            CURLOPT_POSTFIELDS => $postString,
            CURLOPT_TIMEOUT_MS => $this->timeout_ms,
        ];

        curl_setopt_array($ch, $options);

        Curl\Util::execute($ch, $this->retries);
    }
}
