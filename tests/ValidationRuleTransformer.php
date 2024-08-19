<?php

namespace Tests;

class ValidationRuleTransformer
{
    private array $rules;

    private array $output = [];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function toDataProvider(): array
    {
        $this->output = [];

        foreach ($this->rules as $key => $validation)
        {
            if (str_contains($validation, '|'))
            {
                foreach (explode('|', $validation) as $exploded)
                {
                    $this->transformValidation($exploded, $key);
                }
            }
            else
            {
                $this->transformValidation($validation, $key);
            }
        }

        return $this->output;
    }

    private function transformValidation(string $validation, string $key): void
    {
        $other = null;

        if (str_contains($validation, ':'))
        {
            $exploded = explode(':', $validation);
            $validation = $exploded[0];

            if ($validation == 'date_format')
            {
                $other = $exploded[1];
            }

            if ($validation == 'max')
            {
                $other = $exploded[1];
            }
        }

        $array = null;

        switch ($validation)
        {
            case 'email':
            case 'array':
            case 'boolean':
            case 'in':
            case 'phone':
            case 'zipcode':
            case 'date':
            case 'numeric':
            case 'integer':
            case 'file':
                $array = [$key, [$key => 'invalid-'.$validation], $validation];
                break;
            case 'date_format':
                $array = [$key, [$key => 'invalid-'.$validation], $validation, ['format' => $other]];
                break;
            case 'string':
                $array = [$key, [$key => 0], $validation];
                break;
            case 'required':
                $array = [$key, [$key => null], $validation];
                break;
            case 'exists':
                $array = [$key, [$key => 999999999], $validation];
                break;
        }

        if ($array)
        {
            $this->output[] = $array;
        }
    }
}
