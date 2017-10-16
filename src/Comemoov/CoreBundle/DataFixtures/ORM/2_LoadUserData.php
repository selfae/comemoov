<?php


namespace Caher\CoreBundle\DataFixtures\ORM;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var  ContainerInterface container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('cocorico_user.user_manager');

        $nAnimateur = 10;
        while ($nAnimateur > 0) {
            /** @var  User $user */
            $user = $userManager->createUser();
            $user->setUsername('animateur-'.$nAnimateur.'@comeandmoov.com');
            $user->setEmail('animateur-'.$nAnimateur.'@comeandmoov.com');
            $user->setPlainPassword('animateur-'.$nAnimateur.'@comeandmoov.com');
            $user->setLastName($this->_getLastName());
            $user->setFirstName($this->_getFirstName());
            $user->setCountryOfResidence('FR');
            $user->setBirthday(new \DateTime('1973-05-29'));
            $user->setEnabled(true);
            $user->setAnnualIncome(1000);
            $user->setEmailVerified(true);
            $user->setPhoneVerified(true);
            $user->setMotherTongue("fr");

            $event = new UserEvent($user);
            $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
            $user = $event->getUser();

            $userManager->updateUser($user);
            $this->addReference('animateur-'.$nAnimateur, $user);

            $nAnimateur--;
        }

        $nparticipant = 10;
        while ($nparticipant > 0) {
            $user = $userManager->createUser();
            $user->setUsername('participant-'.$nparticipant.'@comeandmoov.com');
            $user->setEmail('participant-'.$nparticipant.'@comeandmoov.com');
            $user->setPlainPassword('12345678');
            $user->setLastName($this->_getLastName());
            $user->setFirstName($this->_getFirstName());
            $user->setCountryOfResidence('FR');
            $user->setBirthday(new \DateTime('1975-08-27'));
            $user->setEnabled(true);
            $user->setAnnualIncome(1000);
            $user->setMotherTongue("fr");

            $event = new UserEvent($user);
            $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
            $user = $event->getUser();

            $userManager->updateUser($user);
            $this->addReference('participant-'.$nparticipant, $user);
            $nparticipant--;
        }

        $user = $userManager->createUser();
        $user->setUsername('disableuser@comeandmoov.com');
        $user->setEmail('disableuser@comeandmoov.com');
        $user->setPlainPassword('disableuser');
        $user->setLastName($this->_getLastName());
        $user->setFirstName($this->_getFirstName());
        $user->setCountryOfResidence('FR');
        $user->setBirthday(new \DateTime('1978-08-27'));
        $user->setEnabled(false);
        $user->setAnnualIncome(1000);
        $user->setMotherTongue("fr");

        $event = new UserEvent($user);
        $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        $userManager->updateUser($user);
        $this->addReference('disable-user', $user);

        $user = $userManager->createUser();
        $user->setLastName('super-admin');
        $user->setFirstName('super-admin');
        $user->setUsername('roxane@comeandmoov.com');
        $user->setEmail('roxane@comeandmoov.com');
        $user->setPlainPassword('super-admin');
        $user->setCountryOfResidence('FR');
        $user->setBirthday(new \DateTime('1978-07-01'));
        $user->setEnabled(true);
        $user->addRole('ROLE_SUPER_ADMIN');

        $event = new UserEvent($user);
        $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        $userManager->updateUser($user);
        $this->addReference('super-admin', $user);
    }

    protected function _getLastName()
    {

        $list = array(
            'Jean',
            'Phillipe',
            'Michel',
            'Alain',
            'Patrick',
            'Nicolas',
            'Marie',
            'Nathalie',
            'Isabelle',
            'Sylvie',
            'Catherine',
            'Martine'
        );
        return $list[rand(0,11)];
    }

    protected function _getFirstName()
    {
        $list = array(
            'Martin',
            'Bernard',
            'Thomas',
            'Petit',
            'Robert',
            'Richard',
            'Durand',
            'Dubois',
            'Moreau',
            'Laurent',
            'Simon',
            'Michel',
            'Lefebvre',
            'Leroy',
            'Roux'
        );
        return $list[rand(0,14)];
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }

}
