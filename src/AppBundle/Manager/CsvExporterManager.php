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

    public static function getCsv($brands, $gmvs)
    {

        $brandGmvArray = CsvExporterManager::getGmvPerBrand($brands);
        $dayGmvArray = CsvExporterManager::getGmvPerDay($gmvs);
        $fullData = array_merge($brandGmvArray, $dayGmvArray);

        $file = 'result.csv';
        $csv = Writer::createFromPath($file);
        $csv->insertOne(['1st May 2018 - 7th May 2018', 'Total GMV']);
        $csv->insertAll($fullData);
        $csv->output($file);

        die;
    }

    private static function getGmvPerBrand($brands)
    {

        $brandGmvArray = array();
        $brandGmvExcVatArray = array();

        // Loop through each brand
        foreach ($brands as $brand) {

            //Get brand's gross merch. values
            $brandGmvs = $brand->getGmvs();
            $brandGmvTotal = 0;

            //Loop through all gross merch values
            foreach ($brandGmvs as $brandGmv) {

                //Check if the gmv is between 1st of May and 5th of May
                $isWithinPeriod = CsvExporterManager::isWithinPeriod($brandGmv);

                if ($isWithinPeriod) {
                    // Add all the turnover values per brand
                    $brandGmvTotal = $brandGmvTotal + (float)$brandGmv->getTurnover();
                }
            }

            // Store brand and total turnover
            array_push($brandGmvArray, array(
                'base' => $brand->getName(),
                'total' => "€ " . round($brandGmvTotal, 2)
            ));

            $valueExcVat = (float)($brandGmvTotal * 0.21);

            // Store brand and total turnover excluding VAT
            array_push($brandGmvExcVatArray, array(
                'base' => $brand->getName() . " (exc vat)",
                'total' => "€ " . round(($brandGmvTotal - $valueExcVat), 2)
            ));
        }

        $result = array_merge($brandGmvArray, $brandGmvExcVatArray);

        return $result;

    }

    private static function getGmvPerDay($gmvs)
    {

        $dayGmvArray = array();

        // Loop through each brand
        foreach ($gmvs as $gmv) {

            //Check if the gmv is between 1st of May and 5th of May
            $isWithinPeriod = CsvExporterManager::isWithinPeriod($gmv);
            $gmvDate = $gmv->getDate()->format('Y-m-d');

            if ($isWithinPeriod) {
                if (isset($dayGmvArray[$gmvDate])) {
                    // date key is already set. Add turnover
                    $dayGmvArray[$gmvDate] += (float)$gmv->getTurnover();
                } else {
                    // date key is not yet set. Create it and add first turnover
                    $dayGmvArray[$gmvDate] = (float)$gmv->getTurnover();
                }
            }
        }

        $result = array();

        // Modify array so it has the same structure as turnover per brand so they can be merged
        foreach ($dayGmvArray as $key => $value) {
            array_push($result, array(
                'base' => $key,
                'total' => "€ " . round($value, 2)
            ));
        }

        return $result;
    }

    private static function isWithinPeriod($gmv)
    {

        //Check if the gmv is between 1st of May and 5th of May
        $gmvDateObject = $gmv->getDate();

        $dateBegin = new \DateTime('2018-05-01 00:00:00');
        $dateEnd = new \DateTime('2018-05-07 23:59:59');

        $result = (($gmvDateObject >= $dateBegin) && ($gmvDateObject <= $dateEnd));

        return $result;
    }

}
