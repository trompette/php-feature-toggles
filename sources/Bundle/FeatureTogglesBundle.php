<?php

namespace Trompette\FeatureToggles\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class FeatureTogglesBundle extends Bundle
{
    public function getContainerExtensionClass(): string
    {
        return FeatureTogglesExtension::class;
    }
}
