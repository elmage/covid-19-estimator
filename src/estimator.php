<?php

use Elmage\CovidEstimator\App\Classes\Estimator;

function covid19ImpactEstimator($data)
{
    $estimatorObj = new Estimator($data);
    return $estimatorObj->computeResponse()->getData();
}