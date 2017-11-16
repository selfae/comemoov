<?php

/**
 * Home Frontend Controller
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */

namespace Comemoov\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Controller\Frontend\HomeController as CocoricoHomeController;

use Cocorico\CoreBundle\Repository\ListingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Cocorico\PageBundle\Repository\PageRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class HomeController
 *
 */
class HomeController extends CocoricoHomeController
{
    /**
     * @Route("/", name="cocorico_home")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var PageRepository $page */
        $page = $em->getRepository('CocoricoPageBundle:Page')->findOneBySlug(
            'home',
            $request->getLocale()
        );
        if (!$page) {
            throw new NotFoundHttpException(sprintf('%s page not found.', 'home'));
        }
        /** @var ListingRepository $listingRepository */
        $listingRepository = $this->getDoctrine()->getRepository('CocoricoCoreBundle:Listing');
        $listings = $listingRepository->findByHighestRanking(6, $request->getLocale());

        return $this->render(
            'ComemoovCoreBundle:Frontend\Home:index.html.twig',
            array(
                'listings'  => $listings,
                'page'      => $page,
            )
        );
    }
}
