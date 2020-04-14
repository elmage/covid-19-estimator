<?php

use Elmage\CovidEstimator\App\Classes\Estimator;

function covid19ImpactEstimator($data)
{
    $estimatorObj = new Estimator($data);
    $estimatorObj->computeResponse();

    return $estimatorObj->getData();
}