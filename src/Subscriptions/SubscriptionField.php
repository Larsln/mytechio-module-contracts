<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\Validate;

/**
 * Declarative definition of one type-specific field of a subscription type.
 *
 * Pure data, no code: the core renders the form from it and validates the
 * input server-side. `key` is the key in `SubscriptionData::$attributes`
 * (lowercase letters, digits and underscores, starting with a letter).
 * `options` lists the selectable values of a `Select` field. `maxLength`
 * applies to `Text` and `Secret`, `pattern` (a PCRE including delimiters) to
 * `Text`, `min`/`max` (decimal strings) to `Number`. `listColumn` shows the
 * field as a column in the subscription list; `help` is a hint below the
 * input.
 */
final readonly class SubscriptionField
{
    /**
     * @param  list<array{value: string, label: string}>  $options
     */
    public function __construct(
        public string $key,
        public string $label,
        public SubscriptionFieldKind $kind,
        public bool $required = false,
        public array $options = [],
        public ?int $maxLength = null,
        public ?string $pattern = null,
        public ?string $min = null,
        public ?string $max = null,
        public bool $listColumn = false,
        public ?string $help = null,
    ) {
        if (preg_match('/^[a-z][a-z0-9_]{0,63}$/', $key) !== 1) {
            throw new ContractException("key must match /^[a-z][a-z0-9_]{0,63}$/, got \"{$key}\".");
        }

        $this->validateOptions($kind, $options);
        $this->validateTextConstraints($kind, $maxLength, $pattern);
        $this->validateNumberConstraints($kind, $min, $max);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'kind' => $this->kind->value,
            'required' => $this->required,
            'options' => $this->options,
            'max_length' => $this->maxLength,
            'pattern' => $this->pattern,
            'min' => $this->min,
            'max' => $this->max,
            'list_column' => $this->listColumn,
            'help' => $this->help,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $options
     */
    private function validateOptions(SubscriptionFieldKind $kind, array $options): void
    {
        if ($kind === SubscriptionFieldKind::Select && $options === []) {
            throw new ContractException('options must not be empty for a select field.');
        }

        if ($kind !== SubscriptionFieldKind::Select && $options !== []) {
            throw new ContractException('options are only allowed for a select field.');
        }

        foreach ($options as $option) {
            if (! is_array($option) || ! is_string($option['value'] ?? null) || ! is_string($option['label'] ?? null)) {
                throw new ContractException('every option must be an array with a string value and a string label.');
            }
        }
    }

    private function validateTextConstraints(SubscriptionFieldKind $kind, ?int $maxLength, ?string $pattern): void
    {
        if ($maxLength !== null) {
            if (! in_array($kind, [SubscriptionFieldKind::Text, SubscriptionFieldKind::Secret], true)) {
                throw new ContractException('maxLength is only allowed for text and secret fields.');
            }

            if ($maxLength < 1) {
                throw new ContractException('maxLength must be at least 1.');
            }
        }

        if ($pattern !== null) {
            if ($kind !== SubscriptionFieldKind::Text) {
                throw new ContractException('pattern is only allowed for text fields.');
            }

            if (! self::isValidPattern($pattern)) {
                throw new ContractException("pattern is not a valid regular expression, got \"{$pattern}\".");
            }
        }
    }

    private static function isValidPattern(string $pattern): bool
    {
        set_error_handler(static fn (): bool => true);

        try {
            return preg_match($pattern, '') !== false;
        } finally {
            restore_error_handler();
        }
    }

    private function validateNumberConstraints(SubscriptionFieldKind $kind, ?string $min, ?string $max): void
    {
        if (($min !== null || $max !== null) && $kind !== SubscriptionFieldKind::Number) {
            throw new ContractException('min and max are only allowed for number fields.');
        }

        if ($min !== null) {
            Validate::decimal($min, 'min');
        }

        if ($max !== null) {
            Validate::decimal($max, 'max');
        }
    }
}
