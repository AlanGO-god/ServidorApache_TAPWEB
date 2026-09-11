<?php
class PasswordValidator
{
    private static array $commonPasswords = [
        '123456', 'password', '12345678', 'qwerty', '123456789',
        '12345', '1234', '111111', '1234567', 'contraseña',
        'contrasena123', 'admin', 'iloveyou', 'abc123'
    ];

    public static function validate(string $password, array $policy, ?string $username = null): array
    {
        $rules = [];

        $rules[] = self::rule('minLength', "Debe tener al menos {$policy['minLength']} caracteres",
            strlen($password) >= $policy['minLength']);

        $rules[] = self::rule('maxLength', "No debe exceder {$policy['maxLength']} caracteres",
            strlen($password) <= $policy['maxLength']);

        $rules[] = self::rule('hasUppercase', "Debe incluir al menos una letra mayúscula",
            (bool)preg_match('/\p{Lu}/u', $password));

        $rules[] = self::rule('hasLowercase', "Debe incluir al menos una letra minúscula",
            (bool)preg_match('/\p{Ll}/u', $password));

        $rules[] = self::rule('hasNumber', "Debe incluir al menos un número",
            (bool)preg_match('/[0-9]/', $password));

        $rules[] = self::rule('hasSymbol', "Debe incluir al menos un símbolo especial",
            (bool)preg_match('/[^\p{L}\p{N}]/u', $password));

        $rules[] = self::rule('noCommonPassword', "No debe ser una contraseña de uso común",
            !in_array(strtolower($password), self::$commonPasswords, true));

        $rules[] = self::rule('noSequentialChars', "No debe contener secuencias obvias (abc, 123)",
            !self::hasSequentialChars($password));

        if ($username !== null && $username !== '') {
            $rules[] = self::rule('noUsernameInPassword', "No debe contener el nombre de usuario",
                stripos($password, $username) === false);
        }

        $passed = array_filter($rules, fn($r) => $r['passed']);
        $score = (int)round((count($passed) / count($rules)) * 100);

        $strength = match(true) {
            $score < 20 => 'muy_debil',
            $score < 40 => 'debil',
            $score < 60 => 'media',
            $score < 80 => 'fuerte',
            default => 'muy_fuerte',
        };

        $isValid = count($passed) === count($rules);

        $suggestions = [];
        foreach ($rules as $r) {
            if (!$r['passed']) {
                $suggestions[] = self::suggestionFor($r['rule']);
            }
        }

        return [
            "password" => $password,
            "isValid" => $isValid,
            "strength" => $strength,
            "score" => $score,
            "rules" => $rules,
            "suggestions" => $suggestions
        ];
    }

    private static function rule(string $id, string $description, bool $passed): array
    {
        return ["rule" => $id, "description" => $description, "passed" => $passed];
    }

    private static function hasSequentialChars(string $password): bool
    {
        $lower = strtolower($password);
        $len = strlen($lower);
        for ($i = 0; $i < $len - 2; $i++) {
            $a = $lower[$i]; $b = $lower[$i + 1]; $c = $lower[$i + 2];
            if (ctype_alnum($a) && ctype_alnum($b) && ctype_alnum($c)) {
                $codeA = ord($a); $codeB = ord($b); $codeC = ord($c);
                if (($codeB - $codeA === 1) && ($codeC - $codeB === 1)) return true;
                if (($codeA - $codeB === 1) && ($codeB - $codeC === 1)) return true;
            }
        }
        return false;
    }

    private static function suggestionFor(string $rule): string
    {
        return match($rule) {
            'minLength' => "Usa una contraseña más larga.",
            'maxLength' => "Acorta la contraseña, excede el máximo permitido.",
            'hasUppercase' => "Agrega al menos una letra mayúscula.",
            'hasLowercase' => "Agrega al menos una letra minúscula.",
            'hasNumber' => "Agrega al menos un número.",
            'hasSymbol' => "Agrega al menos un símbolo especial (por ejemplo: !, @, #, $).",
            'noCommonPassword' => "Evita contraseñas de uso común.",
            'noSequentialChars' => "Evita secuencias obvias como '123' o 'abc'.",
            'noUsernameInPassword' => "No incluyas tu nombre de usuario en la contraseña.",
            default => "Revisa esta regla.",
        };
    }
}
?>