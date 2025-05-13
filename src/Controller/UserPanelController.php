<?php

declare(strict_types=1);


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserPanelController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/panel', name: 'app_panel')]
    public function navigation(): Response
    {
        return $this->render('pages/panel.html.twig');
    }
}