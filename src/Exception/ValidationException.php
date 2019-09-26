<?php declare(strict_types=1);

namespace SilerExt\Exception;

class ValidationException extends \Exception
{
    private $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct('validation failure');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
