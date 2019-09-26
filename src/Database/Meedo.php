<?php declare(strict_types=1);

namespace SilerExt\Database;

use Medoo\Medoo;
use Siler\Container;
use SilerExt\Exception\DbException;
use function SilerExt\Config\{config};

function medoo($connection = 'default'): SilerExtMedoo {
    if (!Container\has("db.$connection")) {
        Container\set("db.$connection", new SilerExtMedoo(config("db.$connection")));
    }

    return Container\get("db.$connection");
}

final class SilerExtMedoo extends Medoo
{
    public function get($table, $join = null, $columns = null, $where = null): ?object
    {
        $item = parent::get($table, $join, $columns, $where);

        return $item ? (object) $item : null;
    }

    public function iselect($table, $join, $columns = null, $where = null): array
    {
        $map = [];
        $items = parent::select($table, $join, $columns, $where);

        foreach ($items as $item) {
            $item = (object) $item;
            $map[$item->id] = $item;
        }

        return $map;
    }

    public function loadRelations(string $table, array $items, string $field): array
    {
        return $this->iselect($table, '*', [
            'id' => array_unique(array_column($items, $field)),
        ]);
    }

    // list($userRoles, $roles) = $db->loadMany2ManyRelations($users, 'user/role');
    public function loadMany2ManyRelations(array $items, $x): array
    {
        list($a, $b) = explode('/', $x);

        $joinItems = $this->iselect(str_replace('/', '_', $x), '*', [
            "{$a}_id" => array_unique(array_column($items, 'id')),
        ]);
        $items = $this->loadRelations($b, $joinItems, "{$b}_id");

        return [$joinItems, $items];
    }

    public function loadLastInserts(string $table, int $nb): array
    {
        $lastId = $this->id();
        $lastIds = range($lastId, $lastId + $nb);

        return $this->iselect($table, '*', [
            'id' => $lastIds,
        ]);
    }

    public function throwOnError()
    {
        $error = $this->error();

        if (isset($error[0]) && $error[0] !== '00000') {
            throw new DbException([$error]);
        }
    }
}
