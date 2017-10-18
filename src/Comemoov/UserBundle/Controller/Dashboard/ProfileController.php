<?php

/**
 * Dashboard Profile Controller
 *
 * @bundle     Comemoov\UserBundle
 * @author     Caher <camille.hernoux@gmail.com>
 */



namespace Comemoov\UserBundle\Controller\Dashboard;

use Cocorico\UserBundle\Controller\Dashboard\ProfileController as CocoricoProfileController;

use Cocorico\UserBundle\Entity\UserAddress;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Cocorico\UserBundle\Form\Type\ProfileContactFormType;
use Cocorico\UserBundle\Form\Type\ProfilePaymentFormType;
use Cocorico\UserBundle\Form\Type\ProfileSwitchFormType;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ProfileController
 *
 * @Route("/user")
 */
class ProfileController extends CocoricoProfileController
{
    /**
     * Become Coach group
     *
     * @Route("/become-coach", name="comemoov_user_dashboard_become_coach")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function becomeCoachAction()
    {

        $user = $this->getUser();
        var_dump($user->getRoles());
        //@TODO : Faire proprement de système de message
        $message = "vous êtes déjà un Coach.";
        if ($user->hasRole('ROLE_COACH') === false) {
            $userManager = $this->get('fos_user.user_manager');
            $user->addRole('ROLE_COACH');
            $userManager->updateUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $message = "Félicitation vous êtes devenu Coach !";
        }
        return $this
            ->container
            ->get('templating')
            ->renderResponse(
                'ComemoovCoreBundle:Dashboard/Common:become_coach.html.twig',
                array(
                    'message' => $message,
                )
            );
    }
}
