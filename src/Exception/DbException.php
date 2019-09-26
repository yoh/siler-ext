<?php declare(strict_types=1);

namespace SilerExt\Exception;

class DbException extends \Exception
{
    private $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct('database failure');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
