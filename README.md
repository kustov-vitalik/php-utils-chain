# PHP Chain Library

[![Latest Stable Version](https://poser.pugx.org/vk-php-utils/chain/v/stable)](https://packagist.org/packages/vk-php-utils/chain)
[![Coverage Status](https://coveralls.io/repos/github/kustov-vitalik/php-utils-chain/badge.svg?branch=master)](https://coveralls.io/github/kustov-vitalik/php-utils-chain?branch=master)
[![Latest Unstable Version](https://poser.pugx.org/vk-php-utils/chain/v/unstable)](https://packagist.org/packages/vk-php-utils/chain)
[![Total Downloads](https://poser.pugx.org/vk-php-utils/chain/downloads)](https://packagist.org/packages/vk-php-utils/chain)
[![License](https://poser.pugx.org/vk-php-utils/chain/license)](https://packagist.org/packages/vk-php-utils/chain)
[![composer.lock](https://poser.pugx.org/vk-php-utils/chain/composerlock)](https://packagist.org/packages/vk-php-utils/chain)

The library was created to make working with collections more simple in PHP

## Requirements
- PHP 7.4
- Extensions: json, mbstring

## Installation
```bash
composer require vk-php-utils/chain:^1.0
```

## Chain API
```php
use VKPHPUtils\Chain\Chain;

// Constructors
Chain::of(...$items): Chain          // Initialize mutable chain
Chain::immutable(...$items): Chain   // Initialize immutable chain

// API
toArray(): array;   // Converts the chain to array (applies all chain-functions)
toChain(): Chain;   // Useful with mutable chain. Returns new chain with all current chain functions applied

hasKey($key): bool              // Check exists the key in current chain or no
hasValue($value): bool;         // Check existence of the value in the chain
search($value)                  // Searches position (key) of the value in current chain. Returns null if the value not found
getValue($key)                  // Get value using key
setValue($key, $value): Chain;  // Set value using key
remove($key): Chain;            // Removes element with index eq to $key
isEmpty(): bool                 // Check is the chain empty
size(): int                     // Get the chain size (number of elements)
count(): int                    // Get the chain size (number of elements)
append($any): Chain;            // Appends value into the chain
prepend($any): Chain;           // Prepend value into the chain

// map/reduce methods
filter(?callable $fn = null): Chain         // Filter the chain using function $fn
map(callable $fn): Chain;                   // Applies function $fn to each chain item and returns chain with mapped entities. Index-safe operation
flatMap(callable $fn): Chain;               // Works like {@see map()} but returns iterable set of mapped objects per each the chain's item
reduce(callable $fn, $initialValue = null)  // Calculate aggregate function result

// keys/values
keys(): Chain;                              // Get keys of the chain
values(): Chain;                            // Get chain values

// chain methods
diff(...$elements): Chain;                  // Calculates diff between current chain and passed one
merge(...$elements): Chain;                 // Merges all elements with current chain
intersect(...$elements): Chain;             // Calculates intersection of two sets (current chain and passed one). Non index-safe operation
intersectKeepIndexes(...$elements): Chain;  // Calculates intersection of two sets (current chain and passed one). Index-safe operation
frequencyAnalysis(): Chain;                 // Created histogram/frequency analysis of the chain
flip(): Chain;                              // Flip the chain
mix(): Chain;                               // Mixes the chain
reverse(bool $saveIndex = false): Chain;    // Reverse current chain
unique(bool $saveIndex = false): Chain;     // Unique items of current chain
slice(int $startIncluded, int $stopExcluded, int $step = 1, bool $saveIndex = false): Chain;                // Get chain's chunk
forEach(callable $fn): Chain;               // Just calls $fn for each chain element

// sort methods
sortKeys(?callable $sortFn = null, ?int $direction = SORT_ASC, ?int $sortFlags = null): Chain;              // Sorts chain by keys
sortValues(?callable $sortFn = null, ?int $direction = SORT_ASC, ?int $sortFlags = SORT_REGULAR): Chain;    // Sorts chain by values. Index-safe operation
sortByProperty(string $propertyName, int $direction = SORT_ASC): Chain;                                     // Sorts collection by given property
```

## Run Tests
`./vendor/bin/phpunit ./tests`