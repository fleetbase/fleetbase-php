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

namespace Fleetbase\Sdk\Resources;

use Fleetbase\Sdk\Resource;
use Fleetbase\Sdk\Service;

/**
 * Fleetbase PHP SDK Base Resource
 */
class Place extends Resource
{
    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     * @return void
     */
    public function __constructor(array $attributes = [], ?Service $service = null, array $options = [])
    {
        parent::__construct($attributes, $service, $options);
    }
}
