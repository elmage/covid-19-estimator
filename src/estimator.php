<?php

require_once('EstimatorClass.php');

function covid19ImpactEstimator($data)
{
    $estimatorObj = new Estimator($data);
    $estimatorObj->computeResponse();

    return $estimatorObj->getData();
}