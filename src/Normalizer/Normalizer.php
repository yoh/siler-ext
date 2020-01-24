<?php declare(strict_types=1);

namespace SilerExt\Normalizer;

function normalizeDateTime(?\DateTime $datetime): ?string {
    return $datetime ? $datetime->format('c') : null;
}

function normalizeDate(?\DateTime $datetime): ?string {
    return $datetime ? $datetime->format('Y-m-d') : null;
}

trait NormalizerTrait
{
    public static function normalizeArray(array $items = [], array $context = []): array
    {
        $normalizedArray = [];
        foreach ($items as $item) {
            $normalizedArray[] = self::normalize($item, $context);
        }

        return $normalizedArray;
    }
}
