<?php

/**
 * Listing Search Frontend Controller
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */

namespace Comemoov\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Controller\Frontend\ListingSearchController as CocoricoListingSearchController;

use Cocorico\CoreBundle\Entity\ListingImage;
use Cocorico\CoreBundle\Event\ListingSearchActionEvent;
use Cocorico\CoreBundle\Event\ListingSearchEvents;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Entity\Listing;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ListingSearchController extends CocoricoListingSearchController
{
    /**
     * Listings search result.
     *
     * @Route("/listing/search_result", name="cocorico_listing_search_result")
     * @Method("GET")
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        return $this->render(
            '@CocoricoCore/Frontend/ListingResult/result.html.twig',
            $this->_getSearchResults($request, Listing::STATUS_PUBLISHED)
        );
    }

    /**
     * Listings Dashboard search result.
     *
     * @Route("/dashboard/listing/search_result", name="comemoove_dashboard_listing_search_result")
     * @Method("GET")
     *
     * @Security("has_role('ROLE_COACH')")
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dashboardSearchAction(Request $request)
    {
        return $this->render(
            '@ComemoovCore/Dashboard/Listing/result.html.twig',
            $this->_getSearchResults($request, Listing::STATUS_NEW)
        );
    }
    /**
     * Return search results
     *
     * @param   Request $request
     * @param   String  $status
     * @return  Array
     */
    protected  function _getSearchResults(Request $request, $status)
    {
        $markers = array();
        $resultsIterator = new \ArrayIterator();
        $nbResults = 0;

        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $this->get('cocorico.listing_search_request');
        $form = $this->createSearchResultForm($listingSearchRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listingSearchRequest = $form->getData();

            $results = $this->get("cocorico.listing_search.manager")->search(
                $listingSearchRequest,
                $request->getLocale(),
                $status

            );
            $nbResults = $results->count();
            $resultsIterator = $results->getIterator();
            $markers = $this->getMarkers($request, $results, $resultsIterator);

            //Persist similar listings id
            $listingSearchRequest->setSimilarListings(array_column($markers, 'id'));

            //Persist listing search request in session
            $this->get('session')->set('listing_search_request', $listingSearchRequest);

        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    /** @Ignore */
                    $this->get('translator')->trans($error->getMessage(), $error->getMessageParameters(), 'cocorico')
                );
            }
        }

        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addListingResultItems($this->get('request_stack')->getCurrentRequest(), $listingSearchRequest);

        //Add params to view through event listener
        $event = new ListingSearchActionEvent($request);
        $this->get('event_dispatcher')->dispatch(ListingSearchEvents::LISTING_SEARCH_ACTION, $event);
        $extraViewParams = $event->getExtraViewParams();

        return array_merge(
            array(
                'results' => $resultsIterator,
                'nb_results' => $nbResults,
                'markers' => $markers,
                'listing_search_request' => $listingSearchRequest,
                'pagination' => array(
                    'page' => $listingSearchRequest->getPage(),
                    'pages_count' => ceil($nbResults / $listingSearchRequest->getMaxPerPage()),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all()
                ),
            ),
            $extraViewParams
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createSearchResultForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'listing_search_result',
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createSearchHomeForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'listing_search_home',
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }

    /**
     * @return ListingSearchRequest
     */
    protected function getListingSearchRequest()
    {
        $session = $this->get('session');
        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $session->has('listing_search_request') ?
            $session->get('listing_search_request') :
            $this->get('cocorico.listing_search_request');

        return $listingSearchRequest;
    }

}
