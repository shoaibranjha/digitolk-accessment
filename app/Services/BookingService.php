<?php

namespace App\Services;

use App\Contracts\BookingInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DTApi\Repository\BookingRepository;
use Exception;
use Illuminate\Support\Facades\Config;
use DTApi\Models\Job;
use DTApi\Models\Distance;

class BookingService implements BookingInterface
{
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    public function getUserJobs($request){
        if($user_id = $request->get('user_id')) {

            $response = $this->repository->getUsersJobs($user_id);

        }
        elseif($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID'))
        {
            $response = $this->repository->getAll($request);
        }
        return $response;
    }
    
    public function findTranslatorJob($id){
        $job = $this->repository->with('translatorJobRel.user')->find($id);
        if (!$job) {
            throw new \Exception('Job not found.');
        }
        return $job;
    }
    
    public function storeUser($request){
        $data = $request->all();
        $response = $this->repository->store($request->__authenticatedUser, $data);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    }
    
    public function updateJob($request){
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    }
    
    public function storeJobEmail($request){
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();
        $response = $this->repository->storeJobEmail($data);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    }
    
    public function getUserJobHistory($request){
        if($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return $response;
        }
        return null;
    }
    
    public function acceptJob($request){
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJob($data, $user);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    }
    
    public function acceptJobWithId($request){
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJobWithId($data, $user);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    } 
    
    public function cancelJobAjax($request){
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->cancelJobAjax($data, $user);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    }
    
    public function endJob($request){
        $data = $request->all();
        $response = $this->repository->endJob($data);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    }
    
    public function customerNotCall($request){
        $data = $request->all();
        $response = $this->repository->customerNotCall($data);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    }
    
    public function getPotentialJob($request){
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->getPotentialJobs($user);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    }

    public function distanceFeedFunc($request){
            $data = $request->all();
            
            if (isset($data['distance']) && $data['distance'] != "") {
                $distance = $data['distance'];
        } else {
            $distance = "";
        }
        if (isset($data['time']) && $data['time'] != "") {
            $time = $data['time'];
        } else {
            $time = "";
        }
        if (isset($data['jobid']) && $data['jobid'] != "") {
            $jobid = $data['jobid'];
        }

        if (isset($data['session_time']) && $data['session_time'] != "") {
            $session = $data['session_time'];
        } else {
            $session = "";
        }

        if ($data['flagged'] == 'true') {
            if($data['admincomment'] == '') return "Please, add comment";
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }
        
        if ($data['manually_handled'] == 'true') {
            $manually_handled = 'yes';
        } else {
            $manually_handled = 'no';
        }
        
        if ($data['by_admin'] == 'true') {
            $by_admin = 'yes';
        } else {
            $by_admin = 'no';
        }
        
        if (isset($data['admincomment']) && $data['admincomment'] != "") {
            $admincomment = $data['admincomment'];
        } else {
            $admincomment = "";
        }
        if ($time || $distance) {
            
            $affectedRows = Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }
        
        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            
            $affectedRows1 = Job::where('id', '=', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));
        }
        return 'Record updated!';
    } 
    
    public function reOpen($request){
        $data = $request->all();
        $response = $this->repository->reopen($data);
        if (!$response) {
            throw new \Exception('data not found.');
        }
        return $response;
    } 
    
    public function sendNotificationTrans($request){
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');
        return response(['success' => 'Push sent']);
    } 
    
    public function sendSMSNotificationToTranslator($request){
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    } 
    
}