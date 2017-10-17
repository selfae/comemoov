<?php

/**
 * Listing Categories Frontend Controller
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */

namespace Comemoov\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Controller\Frontend\ListingCategoriesController as CocoricoListingCategoriesController;

use Cocorico\CoreBundle\Entity\Listing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Listing Dashboard category controller.
 *
 * @Route("/listing")
 */
class ListingCategoriesController extends CocoricoListingCategoriesController
{

}
