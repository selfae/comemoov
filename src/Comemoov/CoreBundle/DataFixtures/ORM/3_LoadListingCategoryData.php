<?php

namespace Caher\CoreBundle\DataFixtures\ORM;

use Cocorico\CoreBundle\Entity\ListingCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadListingCategoryData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $category = new ListingCategory();
        $category->translate('fr')->setName('Sport');
        $manager->persist($category);
        $category->mergeNewTranslations();
        $manager->flush();

        foreach ($this->getSportCategories() as $reference => $subCategoryName) {
            $subCategory = new ListingCategory();
            $subCategory->translate('fr')->setName($subCategoryName);
            $subCategory->setParent($category);
            $manager->persist($subCategory);
            $subCategory->mergeNewTranslations();

            $manager->flush();
            $this->addReference($reference, $subCategory);
        }


    }

    public function getSportCategories()
    {
        return array(
            'yoga'          => 'Yoga',
            'pialtes'       => 'Pilates',
            'abdo-fessiers' => 'Abdo-fessiers',
            'pump'          =>'Pump',
            'swedish-fit'   => 'Swedish Fit',
            'step'          => 'Step',
            'crossfit'      => 'CrossFit',
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }

}
