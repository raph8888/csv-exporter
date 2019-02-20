<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;
use League\Csv\Writer;


class CsvExporterManager
{

    protected $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function getCsv($brands, $gmvs)
    {

        $brandGmvArray = array();
        $dayGmvArray = array();

        // Loop through each brand
        foreach ($brands as $brand) {

            //Get brand's gross merch. values
            $brandGmvs = $brand->getGmvs();
            $brandGmvTotal = 0;

            //Loop through all gross merch values
            foreach ($brandGmvs as $brandGmv) {

                //Check if the gmv is between 1st of May and 5th of May
                $gmvDateObject = $brandGmv->getDate();

                $dateBegin = new \DateTime('2018-05-01 00:00:00');
                $dateEnd = new \DateTime('2018-05-05 23:59:59');

                $gmvDate = $brandGmv->getDate()->format('Y-m-d');

                if (($gmvDateObject >= $dateBegin) && ($gmvDateObject <= $dateEnd)) {
                    $brandGmvTotal = $brandGmvTotal + (float)$brandGmv->getTurnover();
                }
            }

            array_push($brandGmvArray, array(
                'base' => $brand->getName(),
                'total' => $brandGmvTotal
            ));
        }


        // Loop through each brand
        foreach ($gmvs as $gmv) {
            //Check if the gmv is between 1st of May and 5th of May
            $gmvDateObject = $gmv->getDate();
            $dateBegin = new \DateTime('2018-05-01 00:00:00');
            $dateEnd = new \DateTime('2018-05-05 23:59:59');

            $gmvDate = $gmv->getDate()->format('Y-m-d');

            if (($gmvDateObject >= $dateBegin) && ($gmvDateObject <= $dateEnd)) {
                if (isset($dayGmvArray[$gmvDate])) {
                    $dayGmvArray[$gmvDate] += (float)$gmv->getTurnover();
                } else {
                    $dayGmvArray[$gmvDate] = (float)$gmv->getTurnover();
                }
            }
        }

        $result = array();

        foreach ($dayGmvArray as $key => $value){
            array_push($result, array(
                'base' => $key,
                'total' => $value
            ));
        }

        $fullData = array_merge($brandGmvArray, $result);

        $file = 'result.csv';

        $csv = Writer::createFromPath($file);
        $csv->insertAll($fullData);
        $csv->output($file);
die();
        return $fullData;
    }

}
