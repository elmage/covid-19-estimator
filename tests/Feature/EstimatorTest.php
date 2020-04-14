<?php

namespace Tests\Feature;

use App\Classes\Estimator;
use App\Exceptions\InvalidNumberException;
use Tests\TestCase;

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



    public function testResult()
    {
        $main = covid19ImpactEstimator($this->testInput);
        $this->assertIsArray($main, "Expected array, got ".gettype($main));


        $estimator = new Estimator($this->testInput);
        $estimator->computeResponse();
        $got = $estimator->getData();

        $this->assertEquals($main, $got);

        $this->assertIsArray($got, "Expected array, got ".gettype($got));

        $this->assertArrayHasKey('impact', $got, 'Expected data array to have key "impact", key not found');
        $this->assertArrayHasKey('severeImpact', $got, 'Expected data array to have key "severeImpact", key not found');

        $this->assertIsArray($got['impact'], "Expected array, got ".gettype($got));
        $this->assertIsArray($got['severeImpact'], "Expected array, got ".gettype($got));

        $this->assertIsInt($got['impact']['currentlyInfected'], 'Expected data.impact.currentlyInfected to be integer, got '.gettype($got['impact']['currentlyInfected']));
        $this->assertIsInt($got['severeImpact']['currentlyInfected'], 'Expected data.severeImpact.currentlyInfected to be integer, got '.gettype($got['severeImpact']['currentlyInfected']));

        $this->assertIsInt($got['impact']['infectionsByRequestedTime'], 'Expected data.impact.infectionsByRequestedTime to be integer, got '.gettype($got['impact']['infectionsByRequestedTime']));
        $this->assertIsInt($got['severeImpact']['infectionsByRequestedTime'], 'Expected data.severeImpact.infectionsByRequestedTime to be integer, got '.gettype($got['severeImpact']['infectionsByRequestedTime']));

        $this->assertIsInt($got['impact']['severeCasesByRequestedTime'], 'Expected data.impact.severeCasesByRequestedTime to be integer, got '.gettype($got['impact']['severeCasesByRequestedTime']));
        $this->assertIsInt($got['severeImpact']['severeCasesByRequestedTime'], 'Expected data.severeImpact.severeCasesByRequestedTime to be integer, got '.gettype($got['severeImpact']['severeCasesByRequestedTime']));

        $this->assertIsInt($got['impact']['hospitalBedsByRequestedTime'], 'Expected data.impact.hospitalBedsByRequestedTime to be integer, got '.gettype($got['impact']['hospitalBedsByRequestedTime']));
        $this->assertIsInt($got['severeImpact']['hospitalBedsByRequestedTime'], 'Expected data.severeImpact.hospitalBedsByRequestedTime to be integer, got '.gettype($got['severeImpact']['hospitalBedsByRequestedTime']));

        $this->assertIsInt($got['impact']['casesForICUByRequestedTime'], 'Expected data.impact.casesForICUByRequestedTime to be integer, got '.gettype($got['impact']['casesForICUByRequestedTime']));
        $this->assertIsInt($got['severeImpact']['casesForICUByRequestedTime'], 'Expected data.severeImpact.casesForICUByRequestedTime to be integer, got '.gettype($got['severeImpact']['casesForICUByRequestedTime']));

        $this->assertIsInt($got['impact']['casesForVentilatorsByRequestedTime'], 'Expected data.impact.casesForVentilatorsByRequestedTime to be integer, got '.gettype($got['impact']['casesForVentilatorsByRequestedTime']));
        $this->assertIsInt($got['severeImpact']['casesForVentilatorsByRequestedTime'], 'Expected data.severeImpact.casesForVentilatorsByRequestedTime to be integer, got '.gettype($got['severeImpact']['casesForVentilatorsByRequestedTime']));

        $this->assertIsInt($got['impact']['dollarsInFlight'], 'Expected data.impact.dollarsInFlight to be integer, got '.gettype($got['impact']['dollarsInFlight']));
        $this->assertIsInt($got['severeImpact']['dollarsInFlight'], 'Expected data.severeImpact.dollarsInFlight to be integer, got '.gettype($got['severeImpact']['dollarsInFlight']));
    }
}