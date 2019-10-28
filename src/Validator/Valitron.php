<?php declare(strict_types=1);

namespace SilerExt\Validation;

use SilerExt\Exception\ValidationException;
use Siler\Http\Request;
use Valitron\Validator as ValitronValidator;

function validate(array $data, array $rules, ?Callable $callback = null): Validator {
    $locale = null;
    if (in_array('fr', array_keys(Request\accepted_locales()), true)) {
        $locale = 'fr';
    }

    $v = new Validator($data, null, $locale);
    $v->mapStringFieldsRules($rules);
    $v->validate();

    if ($callback) {
        $callback($v);
    }

    if(count($v->errors())) {
        throw new ValidationException($v->errors());
    }

    return $v;
}

function sanitize($mixed, $filters = FILTER_SANITIZE_SPECIAL_CHARS) {
    if (is_array($mixed)) {
        return array_map(function($item) use ($filters) {
            return sanitize($item, $filters);
        }, $mixed);
    }

    return filter_var(trim($mixed), $filters);
}

final class Validator extends ValitronValidator
{
    public function subValidate(string $prefix, array $data, array $rules): void {
        $subV = new self($data);
        $subV->mapStringFieldsRules($rules);

        if (!$subV->validate()) {
            foreach ($subV->errors() as $field => $errors) {
                foreach ($errors as $error) {
                    $this->error("$prefix.$field", $error);
                }
            }
        }
    }

    public function subValidateArray(string $prefix, array $data, array $rules): void {
        foreach ($data as $index => $value) {
            $this->subValidate("$prefix.$index", $value, $rules);
        }
    }

    public function mapStringFieldsRules(array $rules)
    {
        $this->mapFieldsRules(array_map(function($fieldRules) {
            return array_map(function($fieldRule) {
                $split = explode(':', $fieldRule);
                [$validator, $options] = [$split[0], $split[1] ?? null];
                if (is_null($options)) {
                    return $validator;
                }

                return array_merge([$validator], (array) explode(',', $options ?? ''));
            }, explode('|', $fieldRules));
        }, $rules));
    }
}
