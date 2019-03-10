<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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

        $from = new \DateTime('2018-05-01 00:00:00');
        $to   = new \DateTime('2018-05-07 23:59:59');

        $repository = $this->getDoctrine()
            ->getRepository(GrossMerchandiseValue::class);

        $query = $repository->createQueryBuilder('gmv')
            ->where('gmv.date BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to )
            ->orderBy('gmv.brand_id', 'ASC')
            ->getQuery();

        $gmvs = $query->getResult();

        $result = CsvExporterManager::getCsv($brands, $gmvs);

        if($result){
            $response = 'success';
        } else {
            $response = 'failed';
        }

        return new Response(
            '<html><body>' . $response . '</body></html>'
        );
    }
    
}
