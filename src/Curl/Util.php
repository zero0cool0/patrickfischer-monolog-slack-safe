<?php

namespace PatrickFischer\Monolog\Curl;

use CurlHandle;

final class Util
{
    /** @var array<int> */
    private static array $retriableErrorCodes = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_HTTP_NOT_FOUND,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    ];

    /**
     * Executes a CURL request with optional retries and exception on failure
     *
     * @param  CurlHandle  $ch curl handler
     * @return bool|string @see curl_exec
     */
    public static function execute(CurlHandle $ch, int $retries = 5): bool|string
    {
        while ($retries > 0) {
            $retries--;
            $curlResponse = curl_exec($ch);
            if ($curlResponse === false) {
                $curlErrno = curl_errno($ch);

                if (false === \in_array($curlErrno, self::$retriableErrorCodes, true) || $retries === 0) {
                    $curlError = curl_error($ch);

                    error_log(sprintf('Curl\Util: Curl error (code %d): %s', $curlErrno, $curlError));
                }
                continue;
            }

            return $curlResponse;
        }
        return false;
    }
}
