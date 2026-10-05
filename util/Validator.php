<?php

declare(strict_types=1);

class Validator
{
    private array $errors = [];

    public function required(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (
            $value === null ||
            (is_string($value) && trim($value) === '')
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function email(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (
            $value !== null &&
            $value !== '' &&
            filter_var($value, FILTER_VALIDATE_EMAIL) === false
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function minLength(
        string $field,
        mixed $value,
        int $minimum,
        string $message
    ): self {
        if (
            is_string($value) &&
            mb_strlen(trim($value)) < $minimum
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function maxLength(
        string $field,
        mixed $value,
        int $maximum,
        string $message
    ): self {
        if (
            is_string($value) &&
            mb_strlen(trim($value)) > $maximum
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function numeric(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (
            $value !== null &&
            $value !== '' &&
            !is_numeric($value)
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function positive(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (
            $value !== null &&
            $value !== '' &&
            (!is_numeric($value) || (float) $value <= 0)
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function nonNegative(
        string $field,
        mixed $value,
        string $message
    ): self {
        if (
            $value !== null &&
            $value !== '' &&
            (!is_numeric($value) || (float) $value < 0)
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function in(
        string $field,
        mixed $value,
        array $allowedValues,
        string $message
    ): self {
        if (
            $value !== null &&
            $value !== '' &&
            !in_array($value, $allowedValues, true)
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function date(
        string $field,
        mixed $value,
        string $message
    ): self {
        if ($value === null || $value === '') {
            return $this;
        }

        if (!is_string($value)) {
            $this->addError($field, $message);

            return $this;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value
        );

        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date === false ||
            (
                is_array($errors) &&
                (
                    $errors['warning_count'] > 0 ||
                    $errors['error_count'] > 0
                )
            ) ||
            $date->format('Y-m-d') !== $value
        ) {
            $this->addError($field, $message);
        }

        return $this;
    }

    public function addError(
        string $field,
        string $message
    ): self {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;

        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}