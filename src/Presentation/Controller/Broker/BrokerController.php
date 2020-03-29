<?php

namespace App\Presentation\Controller\Broker;

use App\Presentation\View\Broker\IndexView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/brokers")
 */
final class BrokerController extends AbstractController
{
    /**
     * @var IndexView
     */
    private $indexView;

    public function __construct(IndexView $indexView)
    {
        $this->indexView = $indexView;
    }

    /**
     * @Route("/", name="broker_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('brokers/index.html.twig', $this->indexView->index());
    }
}
