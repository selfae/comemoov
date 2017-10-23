<?php

/**
 * Listing Availability Frontend Controller
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */

namespace Comemoov\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Controller\Frontend\ListingAvailabilityController as CocoricoListingAvailabilityController;

use Cocorico\CoreBundle\Entity\Listing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Comemoov\CoreBundle\Document\ListingAvailability;
use Comemoov\CoreBundle\Document\ListingAvailabilityTime;

/**
 * Listing Availability controller.
 *
 * @Route("/listing_availabilities")
 */
class ListingAvailabilityController extends CocoricoListingAvailabilityController
{
    /**
     * Lists ListingAvailability Documents
     *
     * @Route("/{listing_id}/{start}/{end}",
     *      name="cocorico_listing_availabilities",
     *      requirements={
     *          "listing_id" = "\d+",
     *          "start"= "^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$",
     *          "end"= "^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$",
     *          "_format"="json"
     *      },
     *      defaults={"_format": "json"}
     * )
     * @Security("is_granted('view', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"})
     *
     * @Method("GET")
     *
     * @param  Request $request
     * @param  Listing $listing
     * @param  string  $start format yyyy-mm-dd
     * @param  string  $end   format yyyy-mm-dd
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, Listing $listing, $start, $end)
    {
        $start = new \DateTime($start);
        $end = new \DateTime($end);

        $availabilities = $this->get("cocorico.listing_availability.manager")->getAvailabilitiesByListingAndDateRange(
            $listing->getId(),
            $start,
            $end,
            "calendar"
        );

        // url test : https://127.0.0.1/app_dev.php/fr/annonce-disponibilitee/713838660/2017-09-25/2017-11-06



        $locale = $request->getLocale();
        //Convert and format prices
        array_walk(
            $availabilities,
            function (&$el, $key, $locale) {
                //var_dump($this->get('fos_user.user_manager')->findUserBy(array('id'=> $el['coach_id'])));
                $el["title"] = $this
                    ->render(
                        'ComemoovCoreBundle:Dashboard/Listing/Avaibility/Calendar:dialog.html.twig',
                        array(
                            'avaibility' => $el,
                            'formated_price' => $this->get('cocorico.twig.core_extension')->formatPriceFilter($el["title"],$locale,0),
                            'coach'     => $this->get('fos_user.user_manager')->findUserBy(array('id'=> $el['coach_id']))
                        )
                    )
                    ->getContent()
                ;
            },
            $locale
        );
        //var_dump($availabilities);
        //exit;
        return new JsonResponse($availabilities);
    }

    /**
     * @Route("/list", name="comemoov_listing_availabilities_list")
     */
    public function ListAction()
    {
        $repository = $this->get('doctrine_mongodb')
            ->getManager()
            ->getRepository('ComemoovCoreBundle:ListingAvailability');

        $listingAvailability = $repository->findAll();

        return $this->render(
            'ComemoovCoreBundle:Dashboard/Listing/Avaibility:list.html.twig',
            array(
                'avaibilities' => $listingAvailability,
            )
        );
    }

    /**
     * @param int $time
     * @return int
     */
    protected function _convertTimeToId($time)
    {
        return (substr($time, 0, 2))*60+substr($time,2,4);
    }

    /**
     * @Route("/addcoach/{availability_id}",
     *      name="comemoov_listing_availability_add_coach"
     * )
     */
    public function AddCoachAction($availability_id)
    {
        //@TODO : Il faudrat vérifier les droit de l'utilisateur pour valider cette action
        $dm             = $this->get('doctrine_mongodb')->getManager();
        $user           = $this->getUser();
        $id             = substr($availability_id, 0,-4);
        $timeStart      = substr($availability_id, strlen($id));
        //@TODO :  Pour l'exemple ce système ne marchera que pour 1h il faudra faire par la suite un système qui allimente les times correctement
        $timeEnd        = $timeStart+60; // On rajoute 1h

        $timeStartId    = $this->_convertTimeToId($timeStart);
        $timeEndId      = $this->_convertTimeToId($timeEnd);
        $availability   = $this->get('doctrine_mongodb')
            ->getRepository('ComemoovCoreBundle:ListingAvailability')
            ->find($id);
        foreach ($availability->getTimes() as $time) {
            if ($timeStartId <= $time->getId() && $time->getId() < $timeEndId) {
                $timeCoachId = $time->getCoachId();
                if (empty($timeCoachId)) {
                    $time->setCoachId($user->getId());
                    $time->setStatus(ListingAvailabilityTime::STATUS_COACHED);
                    $dm->persist($time);
                }
            }
        }
        $dm->flush();
        return new JsonResponse('{"message": "success"}');
    }

    /**
     * @Route("/removecoach/{availability_id}",
     *      name="comemoov_listing_availability_remove_coach"
     * )
     */
    public function removeCoachAction($availability_id)
    {
        //@TODO : Il faudrat vérifier les droit de l'utilisateur pour valider cette action
        $dm             = $this->get('doctrine_mongodb')->getManager();
        $id             = substr($availability_id, 0,-4);
        $timeStart      = substr($availability_id, strlen($id));
        //@TODO :  Pour l'exemple ce système ne marchera que pour 1h il faudra faire par la suite un système qui allimente les times correctement
        $timeEnd        = $timeStart+60; // On rajoute 1h

        $timeStartId    = $this->_convertTimeToId($timeStart);
        $timeEndId      = $this->_convertTimeToId($timeEnd);
        $availability   = $this->get('doctrine_mongodb')
            ->getRepository('ComemoovCoreBundle:ListingAvailability')
            ->find($id);
        foreach ($availability->getTimes() as $time) {
            if ($timeStartId <= $time->getId() && $time->getId() < $timeEndId) {
                $timeCoachId = $time->getCoachId();
                if ( ! empty($timeCoachId)) {
                    $time->setCoachId(null);
                    $time->setStatus(ListingAvailabilityTime::STATUS_AVAILABLE);
                    $dm->persist($time);
                }
            }
        }
        $dm->flush();
        return new JsonResponse('{"message": "success"}');
    }
}
