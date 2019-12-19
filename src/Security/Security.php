<?php declare(strict_types=1);

namespace SilerExt\Security;

use Firebase\JWT\JWT;
use Siler\Http\Request;
use SilerExt\Exception\AccessDeniedException;
use SilerExt\Exception\JwtException;
use function SilerExt\Config\{config};

function encodeJWT(object $data): string
{
    $data->iat = time();
    $data->exp = time() + config('jwt.ttl');

    return JWT::encode((array) $data, config('jwt.secret'));
}

function decodeJWT(string $jwt): object
{
    return JWT::decode($jwt, config('jwt.secret'), ['HS256']);
}

function ensureAuthJWT(): object
{
    try {
        if (!$user = config('jwt.default_user')) {
            $user = decodeJWT(Request\bearer() ?? '');
        }

        return $user;
    } catch (\Exception $e) {
        throw new JwtException('Invalid JWT');
    }
}

function encodePassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function ensureAdmin(object $user)
{
    if (!isAdmin($user)) {
        throw new AccessDeniedException('access denied');
    }
}

function ensureRole(object $user, int $roleId)
{
    if (!isAdmin($user) && !in_array($roleId, $user->roles, true)) {
        throw new AccessDeniedException('access denied');
    }
}

function isAdmin(object $user): bool
{
    return (bool) $user->is_admin ?? false;
}
