<?php

/**
 * This file is part of the fleetbase/fleetbase-php library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license   https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

/**
 * Fleetbase PHP SDK Array Utility Functions
 */
class Arr
{
    /**
     * @template T
     * @param array<array-key, T> $arr
     * @param callable(T): bool $predicate
     * @return bool
     */
    public static function every(array $arr, callable $predicate)
    {
        foreach ($arr as $e) {
            if (!call_user_func($predicate, $e)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @template T
     * @param array<array-key, T> $arr
     * @param callable(T): bool $predicate
     * @return bool
     */
    public static function any(array $arr, callable $predicate)
    {
        return !static::every(
            $arr,
            function ($e) use ($predicate): bool {
                return !call_user_func($predicate, $e);
            }
        );
    }

    /**
     * @template T
     * @param array<array-key, T> $arr
     * @return T|-1
     */
    public static function first(array $arr)
    {
        $arr = array_values($arr);
        return $arr[0] ?? -1;
    }
}
