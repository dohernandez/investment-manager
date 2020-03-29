<?php

namespace App\Presentation\Controller;

use App\Presentation\View\HomePageView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

final class HomePageController extends AbstractController
{
    /**
     * @var HomePageView
     */
    private $homePageView;

    public function __construct(HomePageView $homePageView)
    {
        $this->homePageView = $homePageView;
    }

    /**
     * @Route("/", name="homepage")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        // replace this example code with whatever you need
        return $this->render('homepage/index.html.twig', $this->homePageView->index());
    }
}
