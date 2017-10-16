<?php

namespace Caher\CoreBundle\DataFixtures\ORM;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Entity\ListingCharacteristic;
use Cocorico\CoreBundle\Entity\ListingCharacteristicValue;
use Cocorico\CoreBundle\Entity\ListingImage;
use Cocorico\CoreBundle\Entity\ListingListingCategory;
use Cocorico\CoreBundle\Entity\ListingListingCharacteristic;
use Cocorico\CoreBundle\Entity\ListingLocation;
use Cocorico\CoreBundle\Entity\ListingTranslation;
use Cocorico\GeoBundle\Entity\Area;
use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Coordinate;
use Cocorico\GeoBundle\Entity\Country;
use Cocorico\GeoBundle\Entity\Department;
use Cocorico\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadListingData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        //GeoGraphical entities
        $country = new Country();
        $country->setCode("FR");
        $country->translate('fr')->setName('France');

        $area = new Area();
        $area->setCountry($country);
        $area->translate('fr')->setName('Île-de-France');

        $department = new Department();
        $department->setCountry($country);
        $department->setArea($area);
        $department->translate('fr')->setName('Île-de-France');

        $city = new City();
        $city->setCountry($country);
        $city->setArea($area);
        $city->setDepartment($department);
        $city->translate('fr')->setName('Paris');

        $manager->persist($country);
        $manager->persist($area);
        $manager->persist($department);
        $manager->persist($city);
        $country->mergeNewTranslations();
        $area->mergeNewTranslations();
        $department->mergeNewTranslations();
        $city->mergeNewTranslations();


        
        $locations = $this->getLocations();

        $nAnnonce = 10;
        while ($nAnnonce > 0) {

            //Coordinate entity
            $coordinate = new Coordinate();
            $coordinate->setCountry($country);
            $coordinate->setArea($area);
            $coordinate->setDepartment($department);
            $coordinate->setCity($city);
            $coordinate->setZip($locations[$nAnnonce]['zip']);
            $coordinate->setRoute($locations[$nAnnonce]['route']);
            $coordinate->setStreetNumber($locations[$nAnnonce]['number']);
            $coordinate->setAddress($locations[$nAnnonce]['number'].' '.$locations[$nAnnonce]['route'].', '.$locations[$nAnnonce]['zip'].' '.$locations[$nAnnonce]['city'].', France');
            $coordinate->setLat($locations[$nAnnonce]['lat']);
            $coordinate->setLng($locations[$nAnnonce]['lng']);
            $manager->persist($coordinate);
            //Listing Location
            $location = new ListingLocation();
            $location->setCountry($locations[$nAnnonce]['country']);
            $location->setCity($locations[$nAnnonce]['city']);
            $location->setZip($locations[$nAnnonce]['zip']);
            $location->setRoute($locations[$nAnnonce]['route']);
            $location->setStreetNumber($locations[$nAnnonce]['number']);
            $location->setCoordinate($coordinate);
            $manager->persist($location);

            //Listing Image
            $image1 = new ListingImage();
            $image1->setName(ListingImage::IMAGE_DEFAULT);
            $image1->setPosition(1);

            $image2 = new ListingImage();
            $image2->setName(ListingImage::IMAGE_DEFAULT);
            $image2->setPosition(2);

            //Listing
            $listing = new Listing();
            $listing->setLocation($location);
            $listing->addImage($image1);
            $listing->addImage($image2);
            $listing->translate('fr')->setTitle('Annonce '.$nAnnonce);

            $listing->translate('fr')->setDescription('Description de l\'annonce '.$nAnnonce);
            $listing->setStatus(Listing::STATUS_NEW);
            $listing->setPrice(1000);
            $listing->setCertified(1);

            /** @var User $user */
            $user = $manager->merge($this->getReference('animateur-'.rand(1,10)));
            $listing->setUser($user);

            /** @var ListingCategory $category */
            $sportReference = array(
                'yoga',
                'pialtes',
                'abdo-fessiers',
                'pump',
                'swedish-fit',
                'step',
                'crossfit',
            );

            $category = $manager->merge($this->getReference($sportReference[rand(0,6)]));
            $listingCategory = new ListingListingCategory();
            $listingCategory->setListing($listing);
            $listingCategory->setCategory($category);
            $listing->addListingListingCategory($listingCategory);

            /** @var ListingCharacteristic $characteristic */
            $characteristic = $manager->merge($this->getReference('characteristic_1'));
            $listingListingCharacteristic = new ListingListingCharacteristic();
            $listingListingCharacteristic->setListing($listing);
            $listingListingCharacteristic->setListingCharacteristic($characteristic);
            /** @var ListingCharacteristicValue $value */
            $value = $manager->merge($this->getReference('characteristic_value_yes'));
            $listingListingCharacteristic->setListingCharacteristicValue($value);
            $listing->addListingListingCharacteristic($listingListingCharacteristic);


            $characteristic = $manager->merge($this->getReference('characteristic_2'));
            $listingListingCharacteristic = new ListingListingCharacteristic();
            $listingListingCharacteristic->setListing($listing);
            $listingListingCharacteristic->setListingCharacteristic($characteristic);
            $value = $manager->merge($this->getReference('characteristic_value_2'));
            $listingListingCharacteristic->setListingCharacteristicValue($value);
            $listing->addListingListingCharacteristic($listingListingCharacteristic);


            $characteristic = $manager->merge($this->getReference('characteristic_3'));
            $listingListingCharacteristic = new ListingListingCharacteristic();
            $listingListingCharacteristic->setListing($listing);
            $listingListingCharacteristic->setListingCharacteristic($characteristic);
            $value = $manager->merge($this->getReference('characteristic_value_custom_1'));
            $listingListingCharacteristic->setListingCharacteristicValue($value);
            $listing->addListingListingCharacteristic($listingListingCharacteristic);


            $characteristic = $manager->merge($this->getReference('characteristic_4'));
            $listingListingCharacteristic = new ListingListingCharacteristic();
            $listingListingCharacteristic->setListing($listing);
            $listingListingCharacteristic->setListingCharacteristic($characteristic);
            $value = $manager->merge($this->getReference('characteristic_value_1'));
            $listingListingCharacteristic->setListingCharacteristicValue($value);
            $listing->addListingListingCharacteristic($listingListingCharacteristic);

            $manager->persist($listing);
            $listing->mergeNewTranslations();
            $manager->flush();


            /** @var ListingTranslation $translation */
            foreach ($listing->getTranslations() as $i => $translation) {
                $translation->generateSlug();
            }
            $manager->persist($listing);
            $manager->flush();

            $this->addReference('listing-'.$nAnnonce, $listing);

            $nAnnonce--;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 8;
    }

    public function getLocations()
    {
        return array(
            1 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75009',
                'route'     => 'Rue de Maubeuge',
                'number'    => '26',
                'lat'       => '48.8773438',
                'lng'       => '2.3409603',
            ),
            2 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75013',
                'route'     => 'Rue des Gobelins',
                'number'    => '7',
                'lat'       => '48.8358434',
                'lng'       => '2.3508861',
            ),
            3 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75015',
                'route'     => 'Rue Dulac',
                'number'    => '13',
                'lat'       => '48.8431481',
                'lng'       => '2.3141152',
            ),
            4 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75016',
                'route'     => 'Rue Davioud',
                'number'    => '7',
                'lat'       => '48.8558988',
                'lng'       => '2.269292',
            ),
            5 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75008',
                'route'     => 'Rue La Boétie',
                'number'    => '55',
                'lat'       => '48.8730933',
                'lng'       => '2.3108077',
            ),
            6 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75018',
                'route'     => 'Rue Marcadet',
                'number'    => '161',
                'lat'       => '48.8913219',
                'lng'       => '2.3359244',
            ),
            7 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75019',
                'route'     => 'Passage de Flandre',
                'number'    => '9',
                'lat'       => '48.8870922',
                'lng'       => '2.3714499',
            ),
            8 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75011',
                'route'     => 'Rue Breguet',
                'number'    => '22',
                'lat'       => '48.8574488',
                'lng'       => '2.3715533',
            ),
            9 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75004',
                'route'     => 'Boulevard Henri IV',
                'number'    => '28',
                'lat'       => '48.8506812',
                'lng'       => '2.3635845',
            ),
            10 => array(
                'country'   => 'FR',
                'city'      => 'Paris',
                'zip'       => '75002',
                'route'     => 'rue de la lune',
                'number'    => '9',
                'lat'       => '48.8697174',
                'lng'       => '2.3509855',
            ),
        );
    }

}
