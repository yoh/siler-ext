<?php declare(strict_types=1);

namespace SilerExt\Exception;

class AuthenticationException extends \Exception
{
    private $nbTry;

    public function __construct(int $nbTry)
    {
        $this->nbTry = $nbTry;

        parent::__construct('invalid credentials');
    }

    public function getNbTry(): int
    {
        return $this->nbTry;
    }
}
