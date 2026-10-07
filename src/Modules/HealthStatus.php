<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Health state of a module, as shown on the module overview.
 * Static constructors cover the four possible states.
 */
final readonly class HealthStatus
{
    public const string OK = 'ok';

    public const string WARNING = 'warning';

    public const string ERROR = 'error';

    public const string UNKNOWN = 'unknown';

    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public string $state,
        public string $summary,
        public array $details = [],
    ) {}

    /**
     * @param  array<string, mixed>  $details
     */
    public static function ok(string $summary, array $details = []): self
    {
        return new self(self::OK, $summary, $details);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function warning(string $summary, array $details = []): self
    {
        return new self(self::WARNING, $summary, $details);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function error(string $summary, array $details = []): self
    {
        return new self(self::ERROR, $summary, $details);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function unknown(string $summary, array $details = []): self
    {
        return new self(self::UNKNOWN, $summary, $details);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'state' => $this->state,
            'summary' => $this->summary,
            'details' => $this->details,
        ];
    }
}
