<?php
/**
 * Listing Availability Time Document
 *
 * @bundle     Comemoov\CoreBundle
 * @author     Caher <camille.hernoux@gmail.com>
 *
 * Date: 21/10/17
 * Time: 15:26
 */

namespace Comemoov\CoreBundle\Document;

use Cocorico\CoreBundle\Document\ListingAvailabilityTime as CocoricoListingAvailabilityTime;


use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\EmbeddedDocument()
 * //MongoDB\UniqueIndex(keys={"day"="asc", "id"="asc"})
 */
class ListingAvailabilityTime extends CocoricoListingAvailabilityTime
{
    const STATUS_COACHED        = 4;

    public static $statusValues = array(
        self::STATUS_AVAILABLE      => 'entity.listing_availability.status.available',
        self::STATUS_UNAVAILABLE    => 'entity.listing_availability.status.unavailable',
        self::STATUS_BOOKED         => 'entity.listing_availability.status.booked',
        self::STATUS_COACHED        => 'entity.listing_availability.status.coached'
    );

    /**
     * @MongoDB\Int(nullable="true", name="c")
     * @MongoDB\Index(order="asc")
     */
    protected $coachId;

    /**
     * Get Coach Id
     * @return int
     */
    public function getCoachId()
    {
        return $this->coachId;
    }

    /**
     * Set Coach Id
     *
     * @param int $coachId
     * @return self
     */
    public function setCoachId($coachId)
    {
        $this->coachId = $coachId;
        return $this;
    }

    /**
     * Set status
     *
     * @param int $status
     * @return self
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for availability.status : %s.', $status)
            );
        }
        $this->status = $status;

        return $this;
    }

}