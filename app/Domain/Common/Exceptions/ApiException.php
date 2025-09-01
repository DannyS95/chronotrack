<?php

namespace App\Domain\Common\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class ApiException extends HttpException
{
    protected array $context = [];

    public function __construct(
        int $statusCode,
        string $message,
        array $context = [],
        \Throwable $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
        $this->context = $context;
    }

    public function toArray(): array
    {
        return [
            'type'      => 'API_EXCEPTION',
            'exception' => class_basename(static::class),
            'message'   => $this->getMessage(),
            'context'   => $this->context,
        ];
    }
}
