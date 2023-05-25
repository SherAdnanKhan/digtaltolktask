<?php
namespace DTApi\Helpers;

use Carbon\Carbon;
use DTApi\Models\Job;
use DTApi\Models\User;
use DTApi\Models\Language;
use DTApi\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DTApi\Repository\UserRepository;

class TeHelper
{
    public static function fetchLanguageFromJobId($id)
    {
        $language = Language::findOrFail($id);
        return $language1 = $language->language;
    }

    public static function getUsermeta($user_id, $key = false)
    {
        return $user = UserMeta::where('user_id', $user_id)->first()->$key;
        if (!$key)
            return $user->usermeta()->get()->all();
        else {
            $meta = $user->usermeta()->where('key', '=', $key)->get()->first();
            if ($meta)
                return $meta->value;
            else return '';
        }
    }

    public static function convertJobIdsInObjs($jobs_ids)
    {

        $jobs = array();
        foreach ($jobs_ids as $job_obj) {
            $jobs[] = Job::findOrFail($job_obj->id);
        }
        return $jobs;
    }

    public static function willExpireAt($due_time, $created_at)
    {
        $due_time = Carbon::parse($due_time);
        $created_at = Carbon::parse($created_at);

        $difference = $due_time->diffInHours($created_at);


        if($difference <= 90)
            $time = $due_time;
        elseif ($difference <= 24) {
            $time = $created_at->addMinutes(90);
        } elseif ($difference > 24 && $difference <= 72) {
            $time = $created_at->addHours(16);
        } else {
            $time = $due_time->subHours(48);
        }

        return $time->format('Y-m-d H:i:s');

    }

    
    public function testCreateOrUpdateMethod()
    {
        $userModel = $this->createMock(\DTApi\Models\User::class);
        $userRepository = new UserRepository($userModel);
        $request = Request::create('/users', 'POST', ['name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'customer']);

        $result = $userRepository->createOrUpdate(null, $request);

        $this->assertInstanceOf(\DTApi\Models\User::class, $result);
        // Add more assertions as needed
    }

    public function testEnableMethod()
    {
        $userModel = $this->createMock(\DTApi\Models\User::class);
        $userRepository = new UserRepository($userModel);
        $userId = 1;

        $userRepository->enable($userId);

        // Add assertions to check if the user status is set to '1'
    }

    public function testDisableMethod()
    {
        $userModel = $this->createMock(\DTApi\Models\User::class);
        $userRepository = new UserRepository($userModel);
        $userId = 1;

        $userRepository->disable($userId);

        // Add assertions to check if the user status is set to '0'
    }

    public function testLoggerInitialization()
    {
        $userModel = $this->createMock(\DTApi\Models\User::class);
        
        $userRepository = new UserRepository($userModel);
        
        $logger = $userRepository->getLogger();
        
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testGetTranslatorsMethod()
    {
        $userModel = $this->createMock(\DTApi\Models\User::class);
        $userRepository = new UserRepository($userModel);
        
        $translators = [
            // Create mock translator objects as per your requirements
        ];
        
        $userModel->expects($this->once())
            ->method('where')
            ->with('user_type', 2)
            ->willReturnSelf();
        
        $userModel->expects($this->once())
            ->method('get')
            ->willReturn($translators);
        
        $result = $userRepository->getTranslators();
        
        $this->assertEquals($translators, $result);

    }

}

