<?php

namespace Hwkdo\IntranetAppHwro\Data\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Description
{
    public function __construct(
        public string $description
    ) {}
}
