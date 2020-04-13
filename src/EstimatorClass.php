<?php

class Estimator
{
    /**
     * output containing estimations
     * @var array
     */
    public $data = [];


    public function __construct(array $input)
    {
        $this->data['data'] = $input;
    }

    public function formatOutput()
    {
        $this->calculateCurrentlyInfected();
        $this->calculateInfectionsByRequestedTime();
    }

    protected function calculateCurrentlyInfected()
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
    }


    protected function calculateInfectionsByRequestedTime()
    {
        $impact = $this->getImpact();
        $severeImpact = $this->getSevereImpact();

        if (array_key_exists('currentlyInfected', $impact)) {

            try {

                $impact['currentlyInfected'] = $this->formatAsInt(
                    $impact['currentlyInfected'] *
                    $this->calculateInfectionMultiplier()
                );

                $this->setImpact($impact);

            } catch (InvalidNumberException $exception) {
            }

        }

        if (array_key_exists('currentlyInfected', $severeImpact)) {

            try {

                $severeImpact['currentlyInfected'] = $this->formatAsInt(
                    $severeImpact['currentlyInfected'] *
                    $this->calculateInfectionMultiplier()
                );

                $this->setSevereImpact($severeImpact);

            } catch (InvalidNumberException $exception) {
            }

        }
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
     * @param array $impact
     */
    public function setSevereImpact(array $severeImpact)
    {
        $this->data['severeImpact'] = $impact;
    }


    /**
     * get input data from data property
     * @return array
     */
    public function getInput(): array
    {
        return $this->data['data'];
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
}