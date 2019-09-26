<?php declare(strict_types=1);

namespace SilerExt\Normalizer;

function normalizeDateTime(\DateTime $datetime): string {
    return $datetime->format('c');
}

function normalizeDate(\DateTime $datetime): string {
    return $datetime->format('Y-m-d');
}
