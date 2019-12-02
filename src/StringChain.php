<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain;


use Exception;

/**
 * Class StringChain
 * @package VKPHPUtils\Chain
 */
class StringChain
{
    private string $string;

    private function __construct(string $original)
    {
        $this->string = $original;
    }

    public static function of(string $string): StringChain
    {
        return new self($string);
    }

    /**
     * @param int $length
     * @param string $characterSet
     * @return StringChain
     * @throws Exception
     */
    public static function random(
        int $length = 6,
        string $characterSet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): StringChain {
        $characterSetLength = strlen($characterSet);
        $generatedString = '';

        for ($i = 0; $i < $length; ++$i) {
            $generatedString .= $characterSet[random_int(0, $characterSetLength - 1)];
        }

        return new self($generatedString);
    }

    public function __toString()
    {
        return $this->string;
    }

    public function toUpperCase(): StringChain
    {
        return new self(mb_strtoupper((string)$this));
    }

    public function toLowerCase(): StringChain
    {
        return new self(mb_strtolower((string)$this));
    }

    public function toPascalCase(): StringChain
    {
        return new self(str_replace(' ', '', ucwords(str_replace('_', ' ', (string)$this))));
    }

    public function toUnderScoreCase(): StringChain
    {
        return new self(strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', (string)$this->toCamelCase()), '_')));
    }

    public function toCamelCase(): StringChain
    {
        return new self(lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', (string)$this)))));
    }

    public function capitalize(): StringChain
    {
        return new self(ucfirst(mb_strtolower((string)$this)));
    }

    public function toChain(string $delimiter = ' '): Chain
    {
        if ($delimiter === '') {
            return Chain::immutable(mb_str_split((string)$this));
        }
        return Chain::immutable(explode($delimiter, (string)$this));
    }

    public function trim($chars = " \t\n\r\0\x0B"): StringChain
    {
        return new self(trim((string)$this, $chars));
    }

    public function trimLeft($chars = " \t\n\r\0\x0B"): StringChain
    {
        return new self(ltrim((string)$this, $chars));
    }

    public function trimRight($chars = " \t\n\r\0\x0B"): StringChain
    {
        return new self(rtrim((string)$this, $chars));
    }

    public function subString(int $start, int $length = PHP_INT_MAX): StringChain
    {
        return new self(mb_substr((string)$this, $start, $length));
    }

    /**
     * Position of substring in the string. -1 if not found
     * @param string $subString
     * @param int $offset
     * @return int
     */
    public function indexOf(string $subString, int $offset = 0): int
    {
        return ($position = mb_strpos((string)$this, $subString, $offset)) === false
            ? -1
            : $position;
    }

    public function size(): int
    {
        return mb_strlen((string)$this);
    }

    public function charAt(int $index): string
    {
        return $this->string[$index] ?? '';
    }
}