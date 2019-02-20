<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Manager\CsvExporterManager;

use AppBundle\Entity\Brand;
use AppBundle\Entity\GrossMerchandiseValue;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/getcsv", name="getcsv")
     */
    public function getAction(Request $request)
    {

        $brands = $this->getDoctrine()
            ->getRepository(Brand::class)
            ->findAll();

        $gmvs = $this->getDoctrine()
            ->getRepository(GrossMerchandiseValue::class)
            ->findAll();


        $response = CsvExporterManager::getCsv($brands, $gmvs);

        die();

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
    
}
