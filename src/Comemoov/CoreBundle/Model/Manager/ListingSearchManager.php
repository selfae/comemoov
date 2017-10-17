<?php
/**
 * ListingSearchManager.php
 *
 * Listing Search Manager
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */
namespace Comemoov\CoreBundle\Model\Manager;

use Cocorico\CoreBundle\Model\Manager\ListingSearchManager as CocoricoListingSearchManager;

use Cocorico\CoreBundle\Document\ListingAvailability;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Event\ListingSearchEvent;
use Cocorico\CoreBundle\Event\ListingSearchEvents;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Model\PriceRange;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\CoreBundle\Repository\ListingAvailabilityRepository;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ListingSearchManager extends CocoricoListingSearchManager
{

    /**
     * @param ListingSearchRequest  $listingSearchRequest
     * @param                       $locale
     * @param string                $status
     *
     * @return Paginator|null
     */
    public function search(ListingSearchRequest $listingSearchRequest, $locale, $status = Listing::STATUS_PUBLISHED)
    {
        $searchLocation = $listingSearchRequest->getLocation();
        $viewport = $searchLocation->getBound();

        //Select
        $queryBuilder = $this->getRepository()->getFindQueryBuilder();

        //Select distance
        $queryBuilder
            ->addSelect('GEO_DISTANCE(co.lat = :lat, co.lng = :lng) AS distance')
            ->setParameter('lat', $searchLocation->getLat())
            ->setParameter('lng', $searchLocation->getLng());

        //Where
        $queryBuilder
            ->where('co.lat < :neLat ')
            ->andWhere('co.lat > :swLat ')
            ->andWhere('co.lng < :neLng ')
            ->andWhere('co.lng > :swLng ')
            ->andWhere('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->setParameter('neLat', $viewport["ne"]["lat"])
            ->setParameter('swLat', $viewport["sw"]["lat"])
            ->setParameter('neLng', $viewport["ne"]["lng"])
            ->setParameter('swLng', $viewport["sw"]["lng"])
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', $status);


        //Dates availabilities (from MongoDB)
        $dateRange = $listingSearchRequest->getDateRange();
        if ($dateRange && $dateRange->getStart() && $dateRange->getEnd()) {
            if ($this->listingDefaultStatus == ListingAvailability::STATUS_AVAILABLE) {
                //Get listings unavailable for searched dates
                $listingsUnavailable = $this->getListingsAvailability(
                    $dateRange,
                    $listingSearchRequest->getTimeRange(),
                    $listingSearchRequest->getFlexibility(),
                    null,
                    array(ListingAvailability::STATUS_UNAVAILABLE, ListingAvailability::STATUS_BOOKED)
                );

                if (count($listingsUnavailable)) {
                    $queryBuilder
                        ->andWhere('l.id NOT IN (:listingsUnavailable)')
                        ->setParameter('listingsUnavailable', array_keys($listingsUnavailable));
                }

            } else {//By default listing are unavailable
                //Get listings available for searched dates
                $listingsAvailable = $this->getListingsAvailability(
                    $dateRange,
                    $listingSearchRequest->getTimeRange(),
                    $listingSearchRequest->getFlexibility(),
                    null,
                    array(ListingAvailability::STATUS_AVAILABLE)
                );

                if (count($listingsAvailable)) {
                    $queryBuilder
                        ->andWhere('l.id IN (:listingsAvailable)')
                        ->setParameter('listingsAvailable', array_keys($listingsAvailable));
                } else {
                    $queryBuilder
                        ->andWhere('l.id IN (:listingsAvailable)')
                        ->setParameter('listingsAvailable', array(0));
                }
            }

            //Min/Max durations
            $duration = false;
            if ($this->timeUnitIsDay) {
                $duration = $dateRange->getDuration($this->endDayIncluded);
            } else {
                $timeRange = $listingSearchRequest->getTimeRange();
                if ($timeRange && $timeRange->getStart()->format('H:i') !== $timeRange->getEnd()->format('H:i')
                    && ($timeRange->getStart()->format('H:i') != '00:00')
                ) {
                    $duration = $timeRange->getDuration($this->timeUnit);
                }
            }

            if ($duration !== false) {
                $queryBuilder
                    ->andWhere(
                        "(l.minDuration IS NULL OR  l.minDuration <= :duration ) AND (l.maxDuration IS NULL OR l.maxDuration >= :duration)"
                    )
                    ->setParameter('duration', $duration);
            }

        }

        //Prices
        $priceRange = $listingSearchRequest->getPriceRange();
        if ($priceRange->getMin() && $priceRange->getMax()) {
            $queryBuilder
                ->andWhere('l.price BETWEEN :minPrice AND :maxPrice')
                ->setParameter('minPrice', $priceRange->getMin())
                ->setParameter('maxPrice', $priceRange->getMax());
        }


        //Categories
        $categories = $listingSearchRequest->getCategories();
        if (count($categories)) {
            $queryBuilder
                ->andWhere("llcat.category IN (:categories)")
                ->setParameter("categories", $categories);
        }

        //Characteristics
        $characteristics = $listingSearchRequest->getCharacteristics();
        $characteristics = array_filter($characteristics);
        if (count($characteristics)) {
            $queryBuilderCharacteristics = $this->em->createQueryBuilder();
            $queryBuilderCharacteristics
                ->select('IDENTITY(c.listing)')
                ->from('CocoricoCoreBundle:ListingListingCharacteristic', 'c');

            foreach ($characteristics as $characteristicId => $characteristicValueId) {
                $queryBuilderCharacteristics
                    ->orWhere(
                        "( c.listingCharacteristic = :characteristic$characteristicId AND c.listingCharacteristicValue = :value$characteristicId )"
                    );

                $queryBuilder
                    ->setParameter("characteristic$characteristicId", $characteristicId)
                    ->setParameter("value$characteristicId", intval($characteristicValueId));
            }

            $queryBuilderCharacteristics
                ->groupBy('c.listing')
                ->having("COUNT(c.listing) = :nbCharacteristics");

            $queryBuilder
                ->setParameter("nbCharacteristics", count($characteristics));

            $queryBuilder
                ->leftJoin('l.listingListingCharacteristics', 'llc')
                ->andWhere(
                    $queryBuilder->expr()->in(
                        'l.id',
                        $queryBuilderCharacteristics->getDQL()
                    )
                );
        }

        //Order
        switch ($listingSearchRequest->getSortBy()) {
            case 'price':
                $queryBuilder->orderBy("l.price", "ASC");
                break;
            case 'distance':
                $queryBuilder->orderBy("distance", "ASC");
                break;
            default:
                $queryBuilder->addOrderBy("distance", "ASC");
                break;
        }
        $queryBuilder->addOrderBy("l.averageRating", "DESC");
        $queryBuilder->addOrderBy("l.adminNotation", "DESC");


        $event = new ListingSearchEvent($listingSearchRequest, $queryBuilder);
        $this->dispatcher->dispatch(ListingSearchEvents::LISTING_SEARCH, $event);
        $queryBuilder = $event->getQueryBuilder();

        //Pagination
        if ($listingSearchRequest->getMaxPerPage()) {
            $queryBuilder
                ->setFirstResult(($listingSearchRequest->getPage() - 1) * $listingSearchRequest->getMaxPerPage())
                ->setMaxResults($listingSearchRequest->getMaxPerPage());
        }

        //Query
        $query = $queryBuilder->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        return new Paginator($query);
    }
}