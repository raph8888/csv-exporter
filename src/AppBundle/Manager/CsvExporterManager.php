<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;
use League\Csv\Writer;
use Money\Currency;
use Money\Money;


class CsvExporterManager
{

    protected $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public static function getCsv($brands, $gmvs)
    {
        $brandNames = CsvExporterManager::getFirstRow($brands);
        $dayGmv = CsvExporterManager::getGmvPerDay($gmvs);
        $brandGmv = CsvExporterManager::getGmvPerBrand($brands);

        CsvExporterManager::generateCsv($brandNames, $brandGmv, $dayGmv);
    }


    private static function getFirstRow($brands)
    {
        $brandNames = array('');

        foreach ($brands as $brand) {
            $brandName = $brand->getName();
            array_push($brandNames, $brandName);
        }
        array_push($brandNames, 'Total');

        return $brandNames;
    }

    private static function getGmvPerDay($gmvs)
    {

        $dayGmvArray = array();

        // Loop through each gmv
        foreach ($gmvs as $gmv) {

            $gmvDate = $gmv->getDate()->format('Y-m-d');
            $gmvBrandId = $gmv->getBrandId();
            $gmvValue = $gmv->getTurnover() * 100;

            $dayGmvArray[$gmvDate][0] = $gmvDate;
            $dayGmvArray[$gmvDate][$gmvBrandId] = number_format(($gmvValue / 100), 2, ',', '');

            if (!isset($dayGmvArray[$gmvDate])) {
                // Date key not set
                // First turnover
                $dayGmvArray[$gmvDate][999] = $gmvValue / 100;

            } else {
                // Date key set
                // Add turnover
                $dayGmvArray[$gmvDate][999] += $gmvValue / 100;
            }

        }

        return $dayGmvArray;
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

                $gmvValue = $brandGmv->getTurnover() * 100;

                //Check if the gmv is between 1st of May and 5th of May
                $isWithinPeriod = CsvExporterManager::isWithinPeriod($brandGmv);

                if ($isWithinPeriod) {
                    // Add all the turnover values per brand
                    $brandGmvTotal = $brandGmvTotal + $gmvValue;
                }
            }

            // Store brand and total turnover
            $brandGmvArray[$brand->getId()] = number_format($brandGmvTotal / 100, 2, ',', '');
            $valueExcVat = ($brandGmvTotal / 100) * 0.21;
            $brandGmvExcVatArray[$brand->getId() . " (exc vat)"] = number_format((($brandGmvTotal / 100) - $valueExcVat), 2, ',', '');
        }

        array_unshift($brandGmvArray, 'Total');
        $brandGmvArray[99999] = '';
        array_unshift($brandGmvExcVatArray, 'Total (exc vat)');
        $brandGmvExcVatArray[99999] = '';

        $result = array($brandGmvArray, $brandGmvExcVatArray);

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

    private static function generateCsv($firstRow, $brandGmv, $dayGmv)
    {
        // Create the CSV file
        $file = 'result.csv';
        $csv = Writer::createFromPath($file);

        // Insert first row
        $csv->insertOne($firstRow);

        // Insert each day rows
        ksort($dayGmv);
        foreach ($dayGmv as $key => $value) {
            ksort($value);
            $csv->insertOne($value);
        }

        // Insert each companies totals rows
        foreach ($brandGmv as $key => $value) {
            ksort($value);
            $csv->insertOne($value);
        }

        // generate file
        $csv->output($file);
        die;
    }

}
