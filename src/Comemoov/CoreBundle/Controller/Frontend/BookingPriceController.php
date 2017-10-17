<?php

/**
 * Booking price Frontend Controller
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */

namespace Comemoov\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Controller\Frontend\BookingPriceController as CocoricoBookingPriceController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Booking controller.
 *
 * @Route("/booking")
 */
class BookingPriceController extends CocoricoBookingPriceController
{

}
