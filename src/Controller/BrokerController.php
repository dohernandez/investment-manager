<?php

namespace App\Controller;

use App\Entity\Broker;
use App\Repository\BrokerRepository;
use App\Twig\Parameters\BrokerStockViewParameters;
use App\Twig\Parameters\BrokerViewParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/brokers")
 */
class BrokerController extends AbstractController
{
    /**
     * @var BrokerViewParameters
     */
    private $brokerViewParameters;

    public function __construct(BrokerViewParameters $brokerViewParameters) {
        $this->brokerViewParameters = $brokerViewParameters;
    }

    /**
     * @Route("/", name="broker_index", methods={"GET"})
     *
     * @param BrokerRepository $repo
     *
     * @return Response
     */
    public function index(BrokerRepository $repo): Response
    {
        return $this->render('brokers/index.html.twig', $this->brokerViewParameters->index());
    }

    /**
     * @Route("/{id}/stocks", name="broker_stock_index", methods={"GET"}, options={"expose"=true})
     *
     * @param Broker $broker
     *
     * @return Response
     */
    public function stocks(Broker $broker): Response
    {
        return $this->render('brokers/stocks.html.twig', $this->brokerViewParameters->stocks($broker));
    }
}
