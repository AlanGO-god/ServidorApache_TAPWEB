<?php
class PasswordGenerator
{
    private const SIMILAR = 'il1Lo0O';
    private const AMBIGUOUS_SYMBOLS = "{}[]()/\\'\"`~,;:.<>";

    public static function generate(array $params): array
    {
        $length = $params['length'] ?? 16;
        $includeUpper = $params['includeUppercase'] ?? true;
        $includeLower = $params['includeLowercase'] ?? true;
        $includeNumbers = $params['includeNumbers'] ?? true;
        $includeSymbols = $params['includeSymbols'] ?? true;
        $excludeSimilar = $params['excludeSimilarCharacters'] ?? false;
        $excludeAmbiguous = $params['excludeAmbiguousSymbols'] ?? false;
        $count = $params['count'] ?? 1;

        if ($length < 8 || $length > 128) {
            throw new InvalidArgumentException('length_out_of_range');
        }
        if ($count < 1 || $count > 50) {
            throw new InvalidArgumentException('count_out_of_range');
        }
        if (!$includeUpper && !$includeLower && !$includeNumbers && !$includeSymbols) {
            throw new InvalidArgumentException('no_character_type');
        }

        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*_-+=?';

        if ($excludeSimilar) {
            $upper = str_replace(str_split(self::SIMILAR), '', $upper);
            $lower = str_replace(str_split(self::SIMILAR), '', $lower);
            $numbers = str_replace(str_split(self::SIMILAR), '', $numbers);
        }
        if ($excludeAmbiguous) {
            $symbols = str_replace(str_split(self::AMBIGUOUS_SYMBOLS), '', $symbols);
        }

        $pool = '';
        if ($includeUpper) $pool .= $upper;
        if ($includeLower) $pool .= $lower;
        if ($includeNumbers) $pool .= $numbers;
        if ($includeSymbols) $pool .= $symbols;

        if ($pool === '') {
            throw new RuntimeException('empty_pool');
        }

        $passwords = [];
        for ($p = 0; $p < $count; $p++) {
            $passwords[] = self::randomFromPool($pool, $length);
        }

        return $passwords;
    }

    private static function randomFromPool(string $pool, int $length): string
    {
        $poolLength = strlen($pool);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $pool[random_int(0, $poolLength - 1)];
        }
        return $result;
    }
}
?>