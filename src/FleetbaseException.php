<?php

/**
 * This file is part of the fleetbase/fleetbase-php library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license   https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

/**
 * A general exception for fleetbase/fleetbase-php.
 */
class FleetbaseException extends \RuntimeException
{
    private ?int $statusCode;
    private ?string $errorCode;
    private ?string $requestId;
    private ?string $method;
    private ?string $url;

    /** @var array<string, mixed> */
    private array $details;

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        string $message,
        ?int $statusCode = null,
        ?string $errorCode = null,
        array $details = [],
        ?string $requestId = null,
        ?string $method = null,
        ?string $url = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->details = $details;
        $this->requestId = $requestId;
        $this->method = $method;
        $this->url = $url;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /** @return array<string, mixed> */
    public function getDetails(): array
    {
        return $this->details;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getRequestMethod(): ?string
    {
        return $this->method;
    }

    public function getRequestUrl(): ?string
    {
        return $this->url;
    }
}
