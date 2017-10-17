<?php

/**
 * Currency Frontend Controller
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */

namespace Comemoov\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Controller\Frontend\CurrencyController as CocoricoCurrencyController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Currency controller.
 *
 * @Route("/currency")
 */
class CurrencyController extends CocoricoCurrencyController
{

}
