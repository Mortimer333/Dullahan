<?php

namespace Dullahan\Attribute;

use Attribute;
use Dullahan\ArgumentResolver\HttpErrorManagerAwareResolver;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Constraints\GroupSequence;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestPayload extends MapRequestPayload
{
    /**
     * @param string[]|string|null $_acceptFormat
     * @param array<string, mixed> $_serializationContext
     * @param string|string[]|GroupSequence|GroupSequence[]|null $_validationGroups
     * @param string $_resolver
     */
    public function __construct(
        array|string|null $_acceptFormat = 'json',
        array $_serializationContext = [],
        string|array|null|GroupSequence $_validationGroups = null,
        string $_resolver = HttpErrorManagerAwareResolver::class,
    ) {
        parent::__construct($_acceptFormat, $_serializationContext, $_validationGroups, $_resolver);
    }
}
