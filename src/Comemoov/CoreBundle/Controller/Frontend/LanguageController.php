<?php

/**
 * Language Frontend Controller
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */

namespace Comemoov\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Controller\Frontend\LanguageController as CocoricoLanguageController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Language controller.
 *
 * @Route("/language")
 */
class LanguageController extends CocoricoLanguageController
{

}
