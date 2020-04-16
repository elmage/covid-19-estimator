<?php

namespace Elmage\CovidEstimator\App\Classes;

use Elmage\CovidEstimator\App\Exceptions\InvalidNumberException;

class Estimator
{
    /**
     * output containing estimations
     * @var array
     */
    private $data = [];

    /**
     * bed availability in hospitals for severe COVID-19 positive patients.
     * @var int
     */
    private $bedAvailability = 0.35;

    /**
     * the estimated number of severe positive cases that will require ICU care.
     * @var int
     */
    private $ICUcare = 0.05;

    /**
     * the estimated number of severe positive cases that will require ventilators.
     * @var int
     */
    private $requireVentilators = 0.02;

    /**
     * the estimated number of severe positive cases that will require hospitalization to recover.
     * @var int
     */
    private $requireHospitalization = 0.15;




    public function __construct(array $input)
    {
        $this->data['data'] = $input;
    }

    public function computeResponse(): Estimator
    {
        $this->calculateCurrentlyInfected()
            ->calculateInfectionsByRequestedTime()
            ->calculateSevereCasesByRequestedTime()
            ->calculateHospitalBedsByRequestedTime()
            ->calculateCasesForICUByRequestedTime()
            ->calculateCasesForVentilatorsByRequestedTime()
            ->calculateDollarsInFlight();

        return $this;
    }


    /*----------------------------------------------------------------------------------------------
     *                                  CALCULATOR METHODS
     *---------------------------------------------------------------------------------------------*/


    public function calculateCurrentlyInfected(): Estimator
    {
        $input = $this->getInput();
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        //check if the input array contains 'reportedCases' then calculate 'currentlyInfected' cases
        if (array_key_exists('reportedCases', $input)) {

            try {

                $impact['currentlyInfected'] = $this->formatAsInt($input['reportedCases'] * 10);
                $severeImpact['currentlyInfected'] = $this->formatAsInt($input['reportedCases'] * 50);

                $this->setImpact($impact);
                $this->setSevereImpact($severeImpact);
            } catch (InvalidNumberException $exception) {
            }

        }

        return $this;
    }

    public function calculateInfectionsByRequestedTime(): Estimator
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {

            $multiplier = $this->calculateInfectionMultiplier();

            if (array_key_exists('currentlyInfected', $impact)) {
                $impact['infectionsByRequestedTime'] = $this->formatAsInt(
                    $impact['currentlyInfected'] *
                    $multiplier
                );

                $this->setImpact($impact);
            } else {
                $this->calculateCurrentlyInfected();
                return $this->calculateInfectionsByRequestedTime();
            }

            if (array_key_exists('currentlyInfected', $severeImpact)) {

                $severeImpact['infectionsByRequestedTime'] = $this->formatAsInt(
                    $severeImpact['currentlyInfected'] *
                    $multiplier
                );

                $this->setSevereImpact($severeImpact);
            }

        } catch (InvalidNumberException $exception) {
        }

        return $this;
    }

    public function calculateSevereCasesByRequestedTime(): Estimator
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {
            if (array_key_exists('infectionsByRequestedTime', $impact)) {
                $impact['severeCasesByRequestedTime'] = $this->formatAsInt(
                    $impact['infectionsByRequestedTime'] *
                    $this->requireHospitalization
                );
                $this->setImpact($impact);
            } else {
                $this->calculateInfectionsByRequestedTime();
                return $this->calculateSevereCasesByRequestedTime();
            }

            if (array_key_exists('infectionsByRequestedTime', $severeImpact)) {
                $severeImpact['severeCasesByRequestedTime'] = $this->formatAsInt(
                    $severeImpact['infectionsByRequestedTime'] *
                    $this->requireHospitalization
                );
                $this->setSevereImpact($severeImpact);
            }
        } catch (InvalidNumberException $exception) {
        }

        return $this;
    }

    public function calculateHospitalBedsByRequestedTime(): Estimator
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {
            //calculate estimated number of available beds from input data
            $beds = $this->calculateBedAvailability();


            if (array_key_exists('severeCasesByRequestedTime', $impact)) {

                $impact['hospitalBedsByRequestedTime'] = $this->formatAsInt(
                    $beds - $impact['severeCasesByRequestedTime']
                );

                $this->setImpact($impact);
            } else {
                $this->calculateSevereCasesByRequestedTime();
                return $this->calculateHospitalBedsByRequestedTime();
            }


            if (array_key_exists('severeCasesByRequestedTime', $severeImpact)) {

                $severeImpact['hospitalBedsByRequestedTime'] = $this->formatAsInt(
                    $beds - $severeImpact['severeCasesByRequestedTime']
                );

                $this->setSevereImpact($severeImpact);
            }

        } catch (InvalidNumberException $exception) {
        }

        return $this;
    }

    public function calculateCasesForICUByRequestedTime(): Estimator
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {

            if (array_key_exists('infectionsByRequestedTime', $impact)) {
                $impact['casesForICUByRequestedTime'] = $this->formatAsInt(
                    (int) $impact['infectionsByRequestedTime'] *
                    $this->ICUcare
                );

                $this->setImpact($impact);
            } else {
                $this->calculateInfectionsByRequestedTime();
                return $this->calculateCasesForICUByRequestedTime();
            }

            if (array_key_exists('currentlyInfected', $severeImpact)) {
                $severeImpact['casesForICUByRequestedTime'] = $this->formatAsInt(
                    (int) $severeImpact['infectionsByRequestedTime'] *
                    $this->ICUcare
                );

                $this->setSevereImpact($severeImpact);
            }

        } catch (InvalidNumberException $exception) {
        }

        return $this;
    }

    public function calculateCasesForVentilatorsByRequestedTime(): Estimator
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {

            if (array_key_exists('infectionsByRequestedTime', $impact)) {
                $impact['casesForVentilatorsByRequestedTime'] = $this->formatAsInt(
                    (int) $impact['infectionsByRequestedTime'] *
                    $this->requireVentilators
                );

                $this->setImpact($impact);
            } else {
                $this->calculateInfectionsByRequestedTime();
                return $this->calculateCasesForVentilatorsByRequestedTime();
            }

            if (array_key_exists('currentlyInfected', $severeImpact)) {
                $severeImpact['casesForVentilatorsByRequestedTime'] = $this->formatAsInt(
                    (int) $severeImpact['infectionsByRequestedTime'] *
                    $this->requireVentilators
                );

                $this->setSevereImpact($severeImpact);
            }

        } catch (InvalidNumberException $exception) {
        }

        return $this;
    }

    public function calculateDollarsInFlight(): Estimator
    {
        $input = $this->getInput();
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {
            $days = $this->calculateDays();

            if (array_key_exists('infectionsByRequestedTime', $impact) && array_key_exists('region', $input)) {
                $impact['dollarsInFlight'] = $this->formatAsInt(
                    (
                        $impact['infectionsByRequestedTime'] *
                        (float) $input['region']['avgDailyIncomePopulation'] *
                        (float) $input['region']['avgDailyIncomeInUSD']
                    )
                    / $days
                );

                $this->setImpact($impact);
            } else {
                if (!array_key_exists('infectionsByRequestedTime', $impact)) {
                    $this->calculateInfectionsByRequestedTime();
                    return $this->calculateDollarsInFlight();
                }
            }

            if (array_key_exists('infectionsByRequestedTime', $severeImpact) && array_key_exists('region', $input)) {
                $severeImpact['dollarsInFlight'] = $this->formatAsInt(
                    (
                        $severeImpact['infectionsByRequestedTime'] *
                        (float) $input['region']['avgDailyIncomePopulation'] *
                        (float) $input['region']['avgDailyIncomeInUSD']
                    )
                    / $days
                );

                $this->setSevereImpact($severeImpact);
            }

        } catch (InvalidNumberException $exception) {
        }

        return $this;
    }



    /*----------------------------------------------------------------------------------------------
     *                                  HELPER METHODS
     *---------------------------------------------------------------------------------------------*/


    /**
     * Calculate estimated available beds
     * @return int
     * @throws InvalidNumberException
     */
    public function calculateBedAvailability()
    {
        $input = $this->getInput();

        return $input['totalHospitalBeds'] * $this->bedAvailability;
    }


    /**
     * normalizes 'weeks' and 'months' from the input data to 'days'
     * @return int
     * @throws InvalidNumberException
     */
    public function calculateDays()
    {
        $input = $this->getInput();

        if (array_key_exists('periodType', $input)) {
            if ($input['periodType'] == "weeks") {
                return $input['timeToElapse'] * 7;
            } elseif ($input['periodType'] == "months") {
                return $input['timeToElapse'] * 30;
            } else {
                return $input['timeToElapse'];
            }
        }

        return 0;
    }

    /**
     * calculate infection multiplier for a time duration
     * @return int
     * @throws InvalidNumberException
     */
    public function calculateInfectionMultiplier()
    {
        $days = $this->calculateDays();
        $factor = $this->formatAsInt($days / 3);

        return pow(2, $factor);
    }

    /**
     * remove decimal part of float like numbers with int typecast
     * @param $number
     * @return int
     * @throws InvalidNumberException
     */
    public function formatAsInt($number): int
    {
        if (!is_numeric($number)) {
            throw new InvalidNumberException("{$number} is not a valid number");
        }

        return (int)$number;
    }


    /*----------------------------------------------------------------------------------------------
     *                                  GETTERS AND SETTERS
     *---------------------------------------------------------------------------------------------*/


    /**
     * get 'impact' from data property
     * @return array
     */
    public function getImpact(): array
    {
        if (!array_key_exists('impact', $this->data)) {
            $this->data['impact'] = [];
        }

        return $this->data['impact'];
    }

    /**
     * set 'impact' in data property
     * @param array $impact
     */
    public function setImpact(array $impact)
    {
        $this->data['impact'] = $impact;
    }

    /**
     * get 'impact' from data property
     * @return array
     */
    public function getSevereImpact(): array
    {
        if (!array_key_exists('severeImpact', $this->data)) {
            $this->data['severeImpact'] = [];
        }

        return $this->data['severeImpact'];
    }

    /**
     * set 'impact' in data property
     * @param array $severeImpact
     */
    public function setSevereImpact(array $severeImpact)
    {
        $this->data['severeImpact'] = $severeImpact;
    }

    /**
     * get input data from data property
     * @return array
     */
    public function getInput(): array
    {
        return $this->data['data'];
    }

    public function getBedAvailability(): float
    {
        return $this->bedAvailability;
    }

    public function setBedAvailability(int $bedAvailability)
    {
        $this->bedAvailability = $bedAvailability/100;
    }

    public function getRequireVentilators(): float
    {
        return $this->requireVentilators;
    }

    public function setRequireVentilators(int $requireVentilators)
    {
        $this->requireVentilators = $requireVentilators/100;
    }

    public function getRequireHospitalization(): float
    {
        return $this->requireHospitalization;
    }

    public function setRequireHospitalization(int $requireHospitalization)
    {
        $this->requireHospitalization = $requireHospitalization/100;
    }

    public function getICUcare(): float
    {
        return $this->ICUcare;
    }

    public function setICUcare(int $ICUcare)
    {
        $this->ICUcare = $ICUcare/100;
    }

    public function getData(): array
    {
        return $this->data;
    }
}