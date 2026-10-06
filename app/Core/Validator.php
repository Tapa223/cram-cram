<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validation côté serveur. Chaque règle ajoute un message compréhensible par un utilisateur.
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /** @param array<string, mixed> $data */
    public function __construct(private array $data)
    {
    }

    private function value(string $field): string
    {
        $value = $this->data[$field] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    private function add(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->add($field, "Le champ « {$label} » est obligatoire.");
        }
        return $this;
    }

    public function max(string $field, int $max, string $label): self
    {
        if (mb_strlen($this->value($field)) > $max) {
            $this->add($field, "« {$label} » ne doit pas dépasser {$max} caractères.");
        }
        return $this;
    }

    public function min(string $field, int $min, string $label): self
    {
        $value = $this->value($field);
        if ($value !== '' && mb_strlen($value) < $min) {
            $this->add($field, "« {$label} » doit contenir au moins {$min} caractères.");
        }
        return $this;
    }

    public function email(string $field, string $label = 'Adresse e-mail'): self
    {
        $value = $this->value($field);
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->add($field, "L'adresse e-mail saisie n'est pas valide.");
        }
        return $this;
    }

    /** Accepte uniquement les liens http(s) : les schémas javascript:, data:, etc. sont refusés. */
    public function url(string $field, string $label): self
    {
        $value = $this->value($field);
        if ($value !== '' && !is_safe_url($value)) {
            $this->add($field, "« {$label} » doit être une adresse web commençant par http:// ou https://.");
        }
        return $this;
    }

    public function date(string $field, string $label): self
    {
        $value = $this->value($field);
        if ($value === '') {
            return $this;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            $this->add($field, "« {$label} » n'est pas une date valide.");
        }
        return $this;
    }

    public function dateAfter(string $field, string $startField, string $message): self
    {
        $end = $this->value($field);
        $start = $this->value($startField);
        if ($end !== '' && $start !== '' && !isset($this->errors[$field]) && !isset($this->errors[$startField]) && $end < $start) {
            $this->add($field, $message);
        }
        return $this;
    }

    /** @param list<string> $allowed */
    public function in(string $field, array $allowed, string $label, bool $nullable = false): self
    {
        $value = $this->value($field);
        if ($nullable && $value === '') {
            return $this;
        }
        if (!in_array($value, $allowed, true)) {
            $this->add($field, "La valeur choisie pour « {$label} » n'est pas valide.");
        }
        return $this;
    }

    public function error(string $field, string $message): self
    {
        $this->add($field, $message);
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
