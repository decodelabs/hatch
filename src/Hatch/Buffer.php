<?php

/**
 * Hatch
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hatch;

class Buffer implements Representation
{
    public function __construct(
        private string $code
    ) {
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
