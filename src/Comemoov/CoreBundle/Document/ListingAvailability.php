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

use Cocorico\CoreBundle\Document\ListingAvailability as CocoricoListingAvailability;

use Comemoov\CoreBundle\Document\ListingAvailabilityTime as ListingAvailabilityTime;

use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\Document(
 *      collection="listing_availabilities",
 *      repositoryClass="Cocorico\CoreBundle\Repository\ListingAvailabilityRepository"
 * )
 * @MongoDB\UniqueIndex(keys={"listingId"="asc", "day"="asc"})
 *
 * @CocoricoAssert\ListingAvailability()
 */
class ListingAvailability extends CocoricoListingAvailability
{
    /**
     * @MongoDB\EmbedMany(targetDocument="ListingAvailabilityTime", name="ts")
     *
     * @var ArrayCollection $times
     */
    protected $times;
}
