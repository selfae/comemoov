<?php

namespace Comemoov\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ComemoovCoreBundle extends Bundle
{
    public function getParent()
    {
        return 'CocoricoCoreBundle';
    }
}
