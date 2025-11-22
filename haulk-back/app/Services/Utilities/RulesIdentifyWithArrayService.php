<?php

namespace App\Services\Utilities;

class RulesIdentifyWithArrayService
{
    public function identify(array $rules, array $attributes): array
    {
        $result = array_intersect_key($rules, $attributes);

        if (!count($keysForArrayableValues = $this->findKeysWhereValuesIsArray($attributes))) {
            return $result;
        }

        $searchedRules = [];

        foreach ($keysForArrayableValues as $key) {
            foreach ($attributes[$key] as $attrKey => $value) {
                $ruleKey = $key . '.' . $attrKey;
                if (!is_array($value) && isset($rules[$ruleKey])) {
                    $searchedRules[$ruleKey] = $rules[$ruleKey];
                }

                if (is_array($value)) {
                    $searchedRules += $this->findRuleByKeyPattern("/^{$key}\..*/", $rules);
                }
            }
        }



        return $result + $searchedRules;
    }

    protected function findKeysWhereValuesIsArray(array $attributes): array
    {
        $result = [];

        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $result[] = $key;
            }
        }

        return $result;
    }

    protected function findRuleByKeyPattern(string $pattern, array $rules): array
    {
        $result = [];

        foreach ($rules as $name => $rule) {
            if (preg_match($pattern, $name)) {
                $result[$name] = $rule;
            }
        }

        return $result;
    }
}
