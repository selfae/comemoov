<?php
/**
 * Comemove User Bundle
 *
 * @bundle     Comemoov\UserBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */

namespace Comemoov\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ComemoovUserBundle extends Bundle
{
    public function getParent()
    {
        return 'CocoricoUserBundle';
    }
}
