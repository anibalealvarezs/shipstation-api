<?php

declare(strict_types=1);

namespace Anibalealvarezs\ShipStationApi\Support;

use Exception;
use GuzzleHttp\Exception\RequestException;

final class ShipStationErrorClassifier
{
    /**
     * @param mixed $input
     * @return array<string, mixed>
     */
    public static function normalize(mixed $input): array
    {
        $payload = self::extractPayload($input);

        return [
            'message' => self::normalizeString($payload['Message'] ?? null)
                ?? self::normalizeString($payload['ExceptionMessage'] ?? null)
                ?? self::extractMessageFallback($input),
            'status' => self::extractStatusCode($input),
            'raw' => $payload,
        ];
    }

    /**
     * @param mixed $input
     * @return array<string, mixed>
     */
    public static function classify(mixed $input): array
    {
        $normalized = self::normalize($input);
        $message = strtolower((string)($normalized['message'] ?? ''));
        $status = $normalized['status'];

        if (
            in_array($status, [429], true)
            || str_contains($message, 'too many requests')
            || str_contains($message, 'rate limit')
            || str_contains($message, 'throttl')
        ) {
            return [
                'category' => 'retryable',
                'reason' => 'shipstation_rate_limit',
                'should_retry' => true,
                'delay_ms' => 1000,
            ];
        }

        return [
            'category' => 'unknown',
            'reason' => 'shipstation_unknown',
            'should_retry' => false,
            'delay_ms' => 0,
        ];
    }

    public static function isRetryable(mixed $input): bool
    {
        return self::classify($input)['should_retry'] === true;
    }

    /**
     * @param mixed $input
     * @return array<string, mixed>
     */
    private static function extractPayload(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        if ($input instanceof RequestException && $input->hasResponse()) {
            $body = $input->getResponse()->getBody();
            $body->rewind();
            $contents = json_decode($body->getContents(), true);
            $body->rewind();

            return is_array($contents) ? $contents : [];
        }

        if ($input instanceof Exception) {
            $prev = $input->getPrevious();
            if ($prev instanceof RequestException && $prev->hasResponse()) {
                return self::extractPayload($prev);
            }

            $fromMessage = json_decode($input->getMessage(), true);
            return is_array($fromMessage) ? $fromMessage : [];
        }

        if (is_string($input)) {
            $contents = json_decode($input, true);
            return is_array($contents) ? $contents : [];
        }

        return [];
    }

    private static function extractMessageFallback(mixed $input): ?string
    {
        if ($input instanceof Exception) {
            return $input->getMessage();
        }

        return self::normalizeString($input);
    }

    private static function extractStatusCode(mixed $input): ?int
    {
        if ($input instanceof RequestException && $input->hasResponse()) {
            return $input->getResponse()->getStatusCode();
        }

        if ($input instanceof Exception && is_numeric($input->getCode()) && $input->getCode() > 0) {
            return (int)$input->getCode();
        }

        if (is_array($input) && isset($input['status']) && is_numeric($input['status'])) {
            return (int)$input['status'];
        }

        return null;
    }

    private static function normalizeString(mixed $value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $normalized = trim((string)$value);
        return $normalized === '' ? null : $normalized;
    }
}

