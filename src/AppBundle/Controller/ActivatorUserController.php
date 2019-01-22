<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use Symfony\symfony\src\Symfony\Component\Routing;
use FOS\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;


class ActivatorUserController extends Controller
{
    /**
     * @Route("/activate", name="activate")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('activatoruser/index.html.twig');
    }
    /**
     * User activate function
     *
     * @Route(name="user_activate")
     * @Method("POST")
     */
    public function activateUserAction(Request $request)
    {
        $username = $request->request->get('username');
        if (isset($username)) {
            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserByUsername($username);
            $user->setEnabled(1);
            $userManager->updateUser($user);
            $this->getDoctrine()->getManager()->flush();
        }
        return $this->render('default/index.html.twig');
    }
}
