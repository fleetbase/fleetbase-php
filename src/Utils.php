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

use Closure;
use Doctrine\Inflector\InflectorFactory;

/**
 * Fleetbase PHP SDK Utility Functions
 */
class Utils
{
    /** @return string */
    public static function pluralize(string $string)
    {
        $inflector = InflectorFactory::create()->build();
        return $inflector->pluralize($string);
    }

    /** @return string */
    public static function classify(string $string)
    {
        $inflector = InflectorFactory::create()->build();
        return $inflector->classify($string);
    }

    /** @return string */
    public static function createNamespace(string $namespace)
    {
        $words = preg_split('/(?=[A-Z])/', $namespace, -1, PREG_SPLIT_NO_EMPTY);
        if ($words === false) {
            return strtolower(static::pluralize($namespace));
        }
        $namespace = implode('-', $words);
        $namespace = strtolower(Utils::pluralize($namespace));

        return $namespace;
    }

    /**
     * @param mixed $target
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($target, $key, $default = null)
    {
        if (is_null($key) || trim($key) === '') {
            return $target;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($target) && !isset($target[$segment])) {
                return static::value($default);
            }

            if (is_object($target) && !isset($target->{$segment})) {
                return static::value($default);
            }

            if (is_array($target) && isset($target[$segment])) {
                $target = $target[$segment];
            }

            if (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            }
        }

        return $target;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /** @return never */
    public static function dd()
    {
        array_map(function ($x) {
            var_dump($x);
        }, func_get_args());

        die(1);
    }
}
