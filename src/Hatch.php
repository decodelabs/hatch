<?php

/**
 * @package Hatch
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Hatch\Representation\StaticExpression as StaticExpressionRepresentation;
use DecodeLabs\Hatch\Proxy\StaticExpression as StaticExpressionProxy;

class Hatch
{
    /**
     * Export array to PHP
     *
     * @param array<int|string,bool|float|int|array<mixed>|string|StaticExpressionRepresentation|StaticExpressionProxy|null> $values
     */
    public static function exportStaticArray(
        array $values,
        int $level = 1
    ): string {
        $output = '[' . "\n";

        $i = 0;
        $count = count($values);
        $isNumericIndex = array_is_list($values);

        foreach ($values as $key => $val) {
            $output .= str_repeat('    ', $level);

            if (!$isNumericIndex) {
                $output .= '\'' . addslashes((string)$key) . '\' => ';
            }

            if($val instanceof StaticExpressionRepresentation) {
                $output .= (string)$val;
            } elseif ($val instanceof StaticExpressionProxy) {
                $output .= (string)$val->exportToStaticExpression();
            } elseif (is_null($val)) {
                $output .= 'null';
            } elseif (is_array($val)) {
                /** @var array<int|string,bool|float|int|array<mixed>|string|StaticExpressionRepresentation|StaticExpressionProxy|null> $val */
                $output .= self::exportStaticArray($val, $level + 1);
            } elseif (
                is_int($val) ||
                is_float($val)
            ) {
                $output .= $val;
            } elseif (is_bool($val)) {
                $output .= $val ? 'true' : 'false';
            } else {
                $output .= '\'' . addslashes($val) . '\'';
            }

            if (++$i < $count) {
                $output .= ',';
            }

            $output .= "\n";
        }

        $output .= str_repeat('    ', $level - 1) . ']';
        return $output;
    }
}
