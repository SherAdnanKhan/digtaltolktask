<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    protected $repository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    public function index(Request $request)
    {
        $user = $request->get('user_id') ? User::find($request->get('user_id')) : $request->__authenticatedUser;

        if ($user->is('admin') || $user->is('superadmin')) {
            return $this->repository->getAll($request);
        } elseif ($user->is('customer') || $user->is('translator')) {
            return $this->repository->getUsersJobs($user->id);
        }

        return response([]);
    }

    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response($response);
    }

    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response($response);
    }

    public function immediateJobEmail(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->storeJobEmail($data);

        return response($response);
    }

    public function getHistory(Request $request)
    {
        $user_id = $request->get('user_id');

        if ($user_id) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }

        return null;
    }

    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJobWithId($data, $user);

        return response($response);
    }

    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    public function endJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->endJob($data);

        return response($response);
    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->customerNotCall($data);

        return response($response);
    }

    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
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
            if ($data['admincomment'] == '') {
                return "Please, add comment";
            }
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
            Distance::where('job_id', '=', $jobid)->update(['distance' => $distance, 'time' => $time]);
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', '=', $jobid)->update(['admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin]);
        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $jobId = $data['jobid'];
        $job = $this->repository->find($jobId);
        $jobData = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $jobData, '*');

        return response(['success' => 'Push sent']);
    }

    public function resendSMSNotifications(Request $request)
    {
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
