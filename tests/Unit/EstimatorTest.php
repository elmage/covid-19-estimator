<?php

namespace Elmage\CovidEstimator\Tests\Unit;

use Elmage\CovidEstimator\App\Classes\Estimator;
use Elmage\CovidEstimator\App\Exceptions\InvalidNumberException;
use Elmage\CovidEstimator\Tests\TestCase;

class EstimatorTest extends TestCase
{
    protected $testInput = [
        "region" => [
            "name" => "Africa",
            "avgAge" => 19.7,
            "avgDailyIncomeInUSD" => 5,
            "avgDailyIncomePopulation" => 0.71
        ],
        "periodType" => "days",
        "timeToElapse" => 58,
        "reportedCases" => 674,
        "population" => 66622705,
        "totalHospitalBeds" => 1380614
    ];

    public function testFormatAsIntReturnsInt()
    {
        $this->expectException(InvalidNumberException::class);

        $estimator = new Estimator($this->testInput);

        $values = [3.56, "Hello", false, []];

        foreach ($values as $value) {
            $got = $estimator->formatAsInt($value);
            $this->assertIsInt($got, "Expected integer, got ".gettype($got));
        }
    }

    public function testCalculateDaysReturnsValidDays()
    {
        $input = $this->testInput;

        $estimator = new Estimator($input);
        $got = $estimator->calculateDays();

        $this->assertIsInt($got, "Expected integer, got ".gettype($got));
        $this->assertEquals($input['timeToElapse'], $got, "Expected ".$input['timeToElapse']. ", got ".$got);



        $input['periodType'] = "weeks";
        $input['timeToElapse'] = 1;

        $estimator = new Estimator($input);
        $got = $estimator->calculateDays();
        $this->assertEquals($input['timeToElapse']*7, $got, "Expected ".($input['timeToElapse']*7).", got ".$got);


        $input['periodType'] = "months";
        $input['timeToElapse'] = 1;

        $estimator = new Estimator($input);
        $got = $estimator->calculateDays();
        $this->assertEquals($input['timeToElapse']*30, $got, "Expected ".($input['timeToElapse']*30).", got ".$got);


        unset($input['periodType']);
        $estimator = new Estimator($input);
        $got = $estimator->calculateDays();
        $this->assertEquals(0, $got, "Expected 0, got ".$got);

    }

    public function testCalculateBedAvailability()
    {
        $input = $this->testInput;

        $estimator = new Estimator($input);
        $got = $estimator->calculateBedAvailability();

        $this->assertIsNumeric($got, "Expected number, got ".gettype($got));
    }

    public function testCalculateInfectionMultiplierReturnsInteger()
    {
        $estimator = new Estimator($this->testInput);
        $got = $estimator->calculateInfectionMultiplier();

        $this->assertIsInt($got, "Expected integer, got ".gettype($got));
    }

    public function testGetAndSetImpact()
    {
        $estimator = new Estimator($this->testInput);

        $got = $estimator->getImpact();
        $this->assertIsArray($got, "Expected array, got ".gettype($got));
        $this->assertEquals([], $got, "Expected empty array got ".json_encode($got));

        $arr = [
            "reportedCases" => 20
        ];

        $estimator->setImpact($arr);

        $got = $estimator->getImpact();

        $this->assertIsArray($got, "Expected array, got ".gettype($got));
        $this->assertEquals($arr, $got, "Expected ".json_encode($arr)." got ".json_encode($got));
    }

    public function testGetAndSetSeverImpact()
    {
        $estimator = new Estimator($this->testInput);

        $got = $estimator->getSevereImpact();
        $this->assertIsArray($got, "Expected array, got ".gettype($got));
        $this->assertEquals([], $got, "Expected empty array got ".json_encode($got));

        $arr = [
            "reportedCases" => 20
        ];

        $estimator->setSevereImpact($arr);

        $got = $estimator->getSevereImpact();

        $this->assertIsArray($got, "Expected array, got ".gettype($got));
        $this->assertEquals($arr, $got, "Expected ".json_encode($arr)." got ".json_encode($got));
    }

    public function testGetAndSetBedAvailability()
    {
        $estimator = new Estimator($this->testInput);
        $got = $estimator->getBedAvailability();

        $this->assertIsFloat($got, "Expected float, got ".gettype($got));
        $this->assertEquals(35/100, $got, "Expected 35 got ".$got);

        $estimator->setBedAvailability(20);
        $got = $estimator->getBedAvailability();

        $this->assertIsFloat($got, "Expected float, got ".gettype($got));
        $this->assertEquals(20/100, $got, "Expected 20 got ".$got);
    }

    public function testGetAndSetRequireVentilators()
    {
        $estimator = new Estimator($this->testInput);
        $got = $estimator->getRequireVentilators();

        $this->assertIsFloat($got, "Expected float, got ".gettype($got));
        $this->assertEquals(2/100, $got, "Expected 2 got ".$got);

        $estimator->setRequireVentilators(20);
        $got = $estimator->getRequireVentilators();

        $this->assertIsFloat($got, "Expected float, got ".gettype($got));
        $this->assertEquals(20/100, $got, "Expected 20 got ".$got);
    }

    public function testGetAndSetRequireHospitalization()
    {
        $estimator = new Estimator($this->testInput);
        $got = $estimator->getRequireHospitalization();

        $this->assertIsFloat($got, "Expected float, got ".gettype($got));
        $this->assertEquals(15/100, $got, "Expected 2 got ".$got);

        $estimator->setRequireVentilators(20);
        $got = $estimator->getRequireVentilators();

        $this->assertIsFloat($got, "Expected float, got ".gettype($got));
        $this->assertEquals(20/100, $got, "Expected 20 got ".$got);
    }

    public function testGetAndSetICUcare()
    {
        $estimator = new Estimator($this->testInput);
        $got = $estimator->getICUcare();

        $this->assertIsFloat($got, "Expected float, got ".gettype($got));
        $this->assertEquals(5/100, $got, "Expected 2 got ".$got);

        $estimator->setICUcare(20);
        $got = $estimator->getICUcare();

        $this->assertIsFloat($got, "Expected float, got ".gettype($got));
        $this->assertEquals(20/100, $got, "Expected 20 got ".$got);
    }

    public function testCalculateDollarsInFlight()
    {
        $estimator = new Estimator($this->testInput);
        $estimator->calculateDollarsInFlight();

        $got = $estimator->getData();

        $this->assertArrayHasKey('impact', $got);
        $this->assertArrayHasKey('severeImpact', $got);

        $this->assertIsArray($got['impact']);
        $this->assertIsArray($got['severeImpact']);

        $this->assertArrayHasKey('dollarsInFlight', $got['impact']);
        $this->assertArrayHasKey('dollarsInFlight', $got['severeImpact']);

        $this->assertIsInt($got['impact']['dollarsInFlight']);
        $this->assertIsInt($got['severeImpact']['dollarsInFlight']);
    }

    public function testCalculateCasesForVentilatorsByRequestedTime()
    {
        $estimator = new Estimator($this->testInput);
        $estimator->calculateCasesForVentilatorsByRequestedTime();

        $got = $estimator->getData();

        $this->assertArrayHasKey('impact', $got);
        $this->assertArrayHasKey('severeImpact', $got);

        $this->assertIsArray($got['impact']);
        $this->assertIsArray($got['severeImpact']);

        $this->assertArrayHasKey('casesForVentilatorsByRequestedTime', $got['impact']);
        $this->assertArrayHasKey('casesForVentilatorsByRequestedTime', $got['severeImpact']);

        $this->assertIsInt($got['impact']['casesForVentilatorsByRequestedTime']);
        $this->assertIsInt($got['severeImpact']['casesForVentilatorsByRequestedTime']);
    }

    public function testCalculateCasesForICUByRequestedTime()
    {
        $estimator = new Estimator($this->testInput);
        $estimator->calculateCasesForICUByRequestedTime();

        $got = $estimator->getData();

        $this->assertArrayHasKey('impact', $got);
        $this->assertArrayHasKey('severeImpact', $got);

        $this->assertIsArray($got['impact']);
        $this->assertIsArray($got['severeImpact']);

        $this->assertArrayHasKey('casesForICUByRequestedTime', $got['impact']);
        $this->assertArrayHasKey('casesForICUByRequestedTime', $got['severeImpact']);

        $this->assertIsInt($got['impact']['casesForICUByRequestedTime']);
        $this->assertIsInt($got['severeImpact']['casesForICUByRequestedTime']);
    }

    public function testCalculateHospitalBedsByRequestedTime()
    {
        for ($i = 0; $i < 2; $i+=1) {
            $this->testInput['totalHospitalBeds'] = pow($this->testInput['totalHospitalBeds'], $i);

            $estimator = new Estimator($this->testInput);
            $estimator->calculateHospitalBedsByRequestedTime();

            $got = $estimator->getData();

            $this->assertArrayHasKey('impact', $got);
            $this->assertArrayHasKey('severeImpact', $got);

            $this->assertIsArray($got['impact']);
            $this->assertIsArray($got['severeImpact']);

            $this->assertArrayHasKey('hospitalBedsByRequestedTime', $got['impact']);
            $this->assertArrayHasKey('hospitalBedsByRequestedTime', $got['severeImpact']);

            $this->assertIsInt($got['impact']['hospitalBedsByRequestedTime']);
            $this->assertIsInt($got['severeImpact']['hospitalBedsByRequestedTime']);
        }
    }

    public function testCalculateSevereCasesByRequestedTime()
    {
        $estimator = new Estimator($this->testInput);
        $estimator->calculateSevereCasesByRequestedTime();

        $got = $estimator->getData();

        $this->assertArrayHasKey('impact', $got);
        $this->assertArrayHasKey('severeImpact', $got);

        $this->assertIsArray($got['impact']);
        $this->assertIsArray($got['severeImpact']);

        $this->assertArrayHasKey('severeCasesByRequestedTime', $got['impact']);
        $this->assertArrayHasKey('severeCasesByRequestedTime', $got['severeImpact']);

        $this->assertIsInt($got['impact']['severeCasesByRequestedTime']);
        $this->assertIsInt($got['severeImpact']['severeCasesByRequestedTime']);
    }

    public function testCalculateInfectionsByRequestedTime()
    {
        $estimator = new Estimator($this->testInput);
        $estimator->calculateInfectionsByRequestedTime();

        $got = $estimator->getData();

        $this->assertArrayHasKey('impact', $got);
        $this->assertArrayHasKey('severeImpact', $got);

        $this->assertIsArray($got['impact']);
        $this->assertIsArray($got['severeImpact']);

        $this->assertArrayHasKey('infectionsByRequestedTime', $got['impact']);
        $this->assertArrayHasKey('infectionsByRequestedTime', $got['severeImpact']);

        $this->assertIsInt($got['impact']['infectionsByRequestedTime']);
        $this->assertIsInt($got['severeImpact']['infectionsByRequestedTime']);
    }

    public function testCalculateCurrentlyInfected()
    {
        $estimator = new Estimator($this->testInput);
        $estimator->calculateCurrentlyInfected();

        $got = $estimator->getData();

        $this->assertArrayHasKey('impact', $got);
        $this->assertArrayHasKey('severeImpact', $got);

        $this->assertIsArray($got['impact']);
        $this->assertIsArray($got['severeImpact']);

        $this->assertArrayHasKey('currentlyInfected', $got['impact']);
        $this->assertArrayHasKey('currentlyInfected', $got['severeImpact']);

        $this->assertIsInt($got['impact']['currentlyInfected']);
        $this->assertIsInt($got['severeImpact']['currentlyInfected']);
    }
}