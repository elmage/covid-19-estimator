<?php

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
    private $bedAvailability = 35;

    /**
     * the estimated number of severe positive cases
    that will require ICU care.
     * @var int
     */
    private $ICUcare = 5;

    /**
     * the estimated number of severe positive cases that will require ventilators.
     * @var int
     */
    private $requireVentilators = 2;




    public function __construct(array $input)
    {
        $this->data['data'] = $input;
    }

    public function computeResponse()
    {
        $this->calculateCurrentlyInfected();
        $this->calculateInfectionsByRequestedTime();
        $this->calculateSevereCasesByRequestedTime();
        $this->calculateHospitalBedsByRequestedTime();
        $this->calculateCasesForICUByRequestedTime();
        $this->calculateCasesForVentilatorsByRequestedTime();
    }


    /*----------------------------------------------------------------------------------------------
     *                                  CALCULATOR METHODS
     *---------------------------------------------------------------------------------------------*/


    protected function calculateCurrentlyInfected()
    {
        $input = $this->getInput();
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        //check if the input array contains 'reportedCases' then calculate 'currentlyInfected' cases
        if (array_key_exists('reportedCases', $input)) {

            try {

                $impact['currentlyInfected'] = $this->formatAsInt((int) $input['reportedCases'] * 10);
                $severeImpact['currentlyInfected'] = $this->formatAsInt((int) $input['reportedCases'] * 50);

                $this->setImpact($impact);
                $this->setSevereImpact($severeImpact);
            } catch (InvalidNumberException $exception) {
            }

        }
    }

    protected function calculateInfectionsByRequestedTime()
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {

            $multiplier = $this->calculateInfectionMultiplier();

            if (array_key_exists('currentlyInfected', $impact)) {
                $impact['infectionsByRequestedTime,'] = $this->formatAsInt(
                    (int) $impact['currentlyInfected'] *
                    $multiplier
                );

                $this->setImpact($impact);
            }

            if (array_key_exists('currentlyInfected', $severeImpact)) {

                $severeImpact['infectionsByRequestedTime,'] = $this->formatAsInt(
                    (int) $severeImpact['currentlyInfected'] *
                    $multiplier
                );

                $this->setSevereImpact($severeImpact);
            }

        } catch (InvalidNumberException $exception) {
        }
    }

    protected function calculateSevereCasesByRequestedTime()
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {
            if (array_key_exists('infectionsByRequestedTime', $impact)) {
                $impact['severeCasesByRequestedTime'] = $this->formatAsInt((int) $impact['infectionsByRequestedTime'] * (15 / 100));
                $this->setImpact($impact);
            }

            if (array_key_exists('infectionsByRequestedTime', $severeImpact)) {
                $severeImpact['severeCasesByRequestedTime'] = $this->formatAsInt((int) $severeImpact['infectionsByRequestedTime'] * (15 / 100));
                $this->setSevereImpact($severeImpact);
            }
        } catch (InvalidNumberException $exception) {
        }
    }

    protected function calculateHospitalBedsByRequestedTime()
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {
            //calculate estimated number of available beds from input data
            $beds = $this->calculateBedAvailability();


            if (array_key_exists('severeCasesByRequestedTime,', $impact)) {

                // If the severe cases are more less than or equal to the number of available beds,
                // set 'hospitalBedsByRequestedTime' to the number of available beds
                // else set to the excess of 'severeCasesByRequestedTime'

                $impact['hospitalBedsByRequestedTime'] = $this->formatAsInt(
                    (int) $impact['severeCasesByRequestedTime'] <= $beds ?
                        $beds :
                        $beds - (int) $impact['severeCasesByRequestedTime']
                );

                $this->setImpact($impact);
            }


            if (array_key_exists('severeCasesByRequestedTime,', $severeImpact)) {

                // If the severe cases are more less than or equal to the number of available beds,
                // set 'hospitalBedsByRequestedTime' to the number of available beds
                // else set to the excess of 'severeCasesByRequestedTime'

                $severeImpact['hospitalBedsByRequestedTime'] = $this->formatAsInt(
                    (int) $severeImpact['severeCasesByRequestedTime'] <= $beds ?
                        $beds :
                        $beds - (int) $severeImpact['severeCasesByRequestedTime']
                );

                $this->setSevereImpact($severeImpact);
            }



        } catch (InvalidNumberException $exception) {
        }
    }

    protected function calculateCasesForICUByRequestedTime()
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {

            if (array_key_exists('infectionsByRequestedTime', $impact)) {
                $impact['casesForICUByRequestedTime'] = $this->formatAsInt(
                    (int) $impact['infectionsByRequestedTime'] *
                    ($this->ICUcare / 100)
                );

                $this->setImpact($impact);
            }

            if (array_key_exists('currentlyInfected', $severeImpact)) {
                $severeImpact['casesForICUByRequestedTime'] = $this->formatAsInt(
                    (int) $severeImpact['infectionsByRequestedTime'] *
                    ($this->ICUcare / 100)
                );

                $this->setSevereImpact($severeImpact);
            }

        } catch (InvalidNumberException $exception) {
        }
    }

    protected function calculateCasesForVentilatorsByRequestedTime()
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        try {

            if (array_key_exists('infectionsByRequestedTime', $impact)) {
                $impact['casesForVentilatorsByRequestedTime'] = $this->formatAsInt(
                    (int) $impact['infectionsByRequestedTime'] *
                    ($this->requireVentilators / 100)
                );

                $this->setImpact($impact);
            }

            if (array_key_exists('currentlyInfected', $severeImpact)) {
                $severeImpact['casesForVentilatorsByRequestedTime'] = $this->formatAsInt(
                    (int) $severeImpact['infectionsByRequestedTime'] *
                    ($this->requireVentilators / 100)
                );

                $this->setSevereImpact($severeImpact);
            }

        } catch (InvalidNumberException $exception) {
        }
    }

    protected function calculateDollarsInFlight()
    {
        $input = $this->getInput();
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();


    }



    /*----------------------------------------------------------------------------------------------
     *                                  HELPER METHODS
     *---------------------------------------------------------------------------------------------*/


    /**
     * Calculate estimated available beds
     * @return int
     * @throws InvalidNumberException
     */
    protected function calculateBedAvailability(): int
    {
        $input = $this->getInput();

        if (array_key_exists('totalHospitalBeds', $input)) {
            return $this->formatAsInt((int) $input['totalHospitalBeds'] * ($this->bedAvailability / 100)) ;
        }

        return 0;
    }


    /**
     * normalizes 'weeks' and 'months' from the input data to 'days'
     * @return int
     * @throws InvalidNumberException
     */
    protected function calculateDays(): int
    {
        $input = $this->getInput();

        if (array_key_exists('periodType', $input)) {
            if ($input['periodType'] == 'weeks') {
                return $this->formatAsInt($input['timeToElapse']) * 7;
            } elseif ($input['periodType'] == 'months') {
                return $this->formatAsInt($input['timeToElapse']) * 30;
            } else {
                return $this->formatAsInt($input['timeToElapse']);
            }
        }

        return 0;
    }

    /**
     * calculate infection multiplier for a time duration
     * @return int
     * @throws InvalidNumberException
     */
    protected function calculateInfectionMultiplier(): int
    {
        $days = $this->calculateDays();
        $factor = $this->formatAsInt($days / 3);

        return $this->formatAsInt(pow(2, $factor));
    }

    /**
     * remove decimal part of float like numbers with int typecast
     * @param $number
     * @return int
     * @throws InvalidNumberException
     */
    private function formatAsInt($number): int
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

    public function getBedAvailability(): int
    {
        return $this->bedAvailability;
    }

    public function setBedAvailability(int $bedAvailability)
    {
        $this->bedAvailability = $bedAvailability;
    }

    public function getRequireVentilators(): int
    {
        return $this->requireVentilators;
    }

    public function setRequireVentilators(int $requireVentilators)
    {
        $this->requireVentilators = $requireVentilators;
    }

    public function getData(): array
    {
        return $this->data;
    }
}