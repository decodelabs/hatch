<?php

/**
 * @package Hatch
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hatch\Proxy;

use DecodeLabs\Hatch\Representation\StaticExpression as StaticExpressionRepresentation;

interface StaticExpression
{
    public function exportToStaticExpression(): StaticExpressionRepresentation;
}
