<?php

namespace Trompette\FeatureToggles\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FeatureTogglesBundle extends Bundle
{
    public function getContainerExtensionClass()
    {
        return FeatureTogglesExtension::class;
    }
}
