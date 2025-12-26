<?php

namespace Laravel\Mcp\Server\Tools\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class IsReadOnly
{
    public bool $value;

    public function __construct(bool $value = true)
    {
        $this->value = $value;
    }
}
