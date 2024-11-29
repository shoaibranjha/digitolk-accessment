<?php

namespace App\Contracts;

interface BookingInterface 
{
    public function getUserJobs(array $data);
    public function findTranslatorJob(int $id);
    public function storeUser($request);
    public function updateJob($request);
    public function storeJobEmail($request);
    public function getUserJobHistory($request);
    public function acceptJob($request);
    public function acceptJobWithId($request);
    public function cancelJobAjax($request);
    public function endJob($request);
    public function customerNotCall($request);
    public function getPotentialJob($request);
    public function distanceFeedFunc($request);
    public function reOpen($request);
    public function sendNotificationTrans($request);
    public function sendSMSNotificationToTranslator($request);
}
