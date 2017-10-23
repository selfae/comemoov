<?php
/**
 * Listing Availability Manager
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 *
 * Date: 19/10/17
 * Time: 11:07
 */

namespace Comemoov\CoreBundle\Model\Manager;

use Cocorico\CoreBundle\Model\Manager\ListingAvailabilityManager as CocoricoListingAvailabilityManager;

use Comemoov\CoreBundle\Document\ListingAvailability;
use Comemoov\CoreBundle\Document\ListingAvailabilityTime;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\CoreBundle\Repository\ListingAvailabilityRepository;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Query;


class ListingAvailabilityManager extends CocoricoListingAvailabilityManager
{
    /**
     * Get ListingAvailability as calendar event
     * Id of event is equal to the concatenation of $this->getId() and start time
     *
     * @param bck $listingAvailability
     *
     * @return array
     */
    public function asCalendarEvent($listingAvailability)
    {
        /** @var \MongoDate $dayMD */
        $dayMD = $listingAvailability['d'];
        $day = new \DateTime();
        $day->setTimestamp($dayMD->sec);
        $day = $day->format("Y-m-d");

        $timesRanges = $this->getTimesRanges($listingAvailability);

        $events = array();
        if (count($timesRanges)) {
            foreach ($timesRanges as $i => $timeRange) {
                $price = $timeRange['price'] / 100;
                $events[] = array(
                    'id'        => $listingAvailability["_id"] . str_replace(":", "", $timeRange['start']),
                    'title'     => $price,
//                  'description' => "",
                    /** @Ignore */
                    'className' => "cal-" . str_replace(
                            "entity.listing_availability.status.",
                            "",
                            ListingAvailabilityTime::$statusValues[$timeRange['status']]
                        ) . "-evt",
                    'start'     => $day . " " . $timeRange['start'],
                    'end'       => $day . " " . $timeRange['end'],
                    'editable'  => true,
                    'allDay'    => false,
                    'coach_id'  => $timeRange['coach_id'],
                );
            }
        } else {
            $allDay = false;
            if ($this->timeUnitIsDay) {
                $allDay = true;
            }
            $price = $listingAvailability["p"] / 100;
            $events[] = array(
                'id'        => $listingAvailability["_id"] . "0000",
                'title'     => "$price",
//              'description' => "",
                /** @Ignore */
                'className' => "cal-" . str_replace(
                        "entity.listing_availability.status.",
                        "",
                        bck::$statusValues[$listingAvailability["s"]]
                    ) . "-evt",
                'start'     => $day . " " . "00:00",
                'end'       => $day . " " . "23:59",
                'editable'  => true,
                'allDay'    => $allDay,
            );
        }

        return $events;
    }

    /**
     * Construct time ranges from ListingAvailabilityTimes
     *
     * @param bck $listingAvailability
     * @param int                 $addOneMinuteToEndTime 1 or 0
     *
     * @return array
     */
    public function getTimesRanges($listingAvailability, $addOneMinuteToEndTime = 1)
    {
        $times = isset($listingAvailability["ts"]) ? $listingAvailability["ts"] : array();
        $timesRanges = $range = array();
        $prevStatus = $prevId = $prevPrice = false;

        foreach ($times as $i => $time) {
            if ($time["s"] !== $prevStatus || $time["_id"] != ($prevId + 1) || $time["p"] !== $prevPrice) {
                if ($prevStatus !== false && $prevId !== false) {
                    $range['end'] = date('H:i', mktime(0, $prevId + $addOneMinuteToEndTime));
                    $timesRanges[] = $range;
                    //$range = array();
                }

                $range = array(
                    'start' => date('H:i', mktime(0, $time["_id"])),
                    'status' => $time["s"],
                    'price' => $time["p"]
                );
                if ( ! empty($time['c'])) {
                    $range['coach_id'] = $time['c'];
                } else {
                    $range['coach_id'] = null;
                }
            }

            $prevStatus = $time["s"];
            $prevPrice = $time["p"];
            $prevId = $time["_id"];
        }

        if (count($times)) {
            $end = end($times);
            $range['end'] = date('H:i', mktime(0, $end["_id"] + $addOneMinuteToEndTime));
            $timesRanges[] = $range;
        }

        return $timesRanges;
    }
}