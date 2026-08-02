<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public function validate(array $input, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $value = $input[$field] ?? null;
            $ruleSet = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $nullable = in_array('nullable', $ruleSet, true);

            if ($nullable && ($value === null || $value === '')) {
                continue;
            }

            foreach ($ruleSet as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                [$name, $argument] = array_pad(explode(':', (string)$rule, 2), 2, null);
                $stringValue = trim((string)$value);

                if ($name === 'required' && $stringValue === '') {
                    $errors[$field][] = 'This field is required.';
                }
                if ($name === 'email' && $stringValue !== '' && !filter_var($stringValue, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Invalid email address.';
                }
                if ($name === 'numeric' && $stringValue !== '' && !is_numeric($stringValue)) {
                    $errors[$field][] = 'This field must be numeric.';
                }
                if ($name === 'min' && $argument !== null && (float)$stringValue < (float)$argument) {
                    $errors[$field][] = 'Minimum value is ' . $argument . '.';
                }
                if ($name === 'max' && $argument !== null && (float)$stringValue > (float)$argument) {
                    $errors[$field][] = 'Maximum value is ' . $argument . '.';
                }
                if ($name === 'in' && $argument !== null) {
                    $allowed = array_map('trim', explode(',', $argument));
                    if ($stringValue !== '' && !in_array($stringValue, $allowed, true)) {
                        $errors[$field][] = 'Invalid selection.';
                    }
                }
                if ($name === 'min_length' && $argument !== null && strlen($stringValue) < (int)$argument) {
                    $errors[$field][] = 'Minimum length is ' . $argument . '.';
                }
                if ($name === 'max_length' && $argument !== null && strlen($stringValue) > (int)$argument) {
                    $errors[$field][] = 'Maximum length is ' . $argument . '.';
                }
            }
        }

        return $errors;
    }
}
