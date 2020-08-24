<?php declare(strict_types=1);

namespace SilerExt\Exception;

class AuthenticationException extends \Exception
{
    private $nbTry;

    public function __construct(int $nbTry = 1, string $message = 'invalid credentials')
    {
        $this->nbTry = $nbTry;

        parent::__construct($message);
    }

    public function getNbTry(): int
    {
        return $this->nbTry;
    }
}
