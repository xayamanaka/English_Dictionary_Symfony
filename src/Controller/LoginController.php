<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\Session;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
        public function index(AuthenticationUtils $authenticationUtils,Session $session): Response
          {
             // get the login error if there is one
             $error = $authenticationUtils->getLastAuthenticationError();
    
             // last username entered by the user
             $lastUsername = $authenticationUtils->getLastUsername();

             $session->set('name', $lastUsername);
             $lastUsername=$session->get('name');
    
              return $this->render('login/index.html.twig', [

                 'last_username' => $lastUsername,
                 'error'         => $error,
              ]);
          }
      }