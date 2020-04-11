<?php

namespace App\Presentation\Controller\Report;

use App\Presentation\View\Market\Stock\IndexView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reports")
 */
final class WeeklyReportController extends AbstractController
{
    /**
     * @Route("/weekly", name="report_weekly", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return new Response("Weekly Report");
    }
}
