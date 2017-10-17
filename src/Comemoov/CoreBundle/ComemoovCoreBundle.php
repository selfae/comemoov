<?php
/**
 * ComemooveCoreBundle.php
 *
 * Bundle declaration extends CocoricoBundle
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */
namespace Comemoov\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ComemoovCoreBundle extends Bundle
{
    public function getParent()
    {
        return 'CocoricoCoreBundle';
    }
}
