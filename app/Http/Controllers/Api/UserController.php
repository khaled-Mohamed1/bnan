<?php

namespace App\Http\Controllers\Api;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignUserSubscriptionRequest;
use App\Http\Resources\UserSubscriptionResource;

class UserController extends Controller
{
    use ApiResponseTrait;
    public function assignSubscription(AssignUserSubscriptionRequest $request): JsonResponse
    {    

        DB::beginTransaction();
        try {
            $plan = SubscriptionPlan::find($request['subscription_plan_id']);

            if(!$plan)
                return $this->errorResponse('Subscription not exsists',404);

            UserSubscription::query()->create([
                'subscription_plan_id' =>$request->subscription_plan_id,
                'start_date' =>Carbon::now(),
                'end_date'=>Carbon::now()->addDays($plan->duration_days),
                'status'=>'active',
                'user_name'=> $request->user_name,
                'user_email' => $request->user_email,
                'ols_device_limit' =>$plan->device_limit,
                'renewal_option' =>'manual',
            ]);


            DB::commit();

             return $this->successResponse(null,__("messages.MSG-23"));

        } catch (\Exception $e) {
             
             DB::rollBack();
            Log::error('UserController@assignSubscription' . $e->getMessage());
            return $this->errorResponse(__('messages.MSG-18'));
        }
    }
 

    public function showCurrentSubscription(UserSubscription $user_subscription): \Illuminate\Http\JsonResponse
    {

        $user_subscription->load('plan');
        return $this->successResponse(new UserSubscriptionResource($user_subscription),'User Subscription retrived successfully');

       
    }

    public function indexCurrentSubscription(){
       
      return $this->successResponse(UserSubscriptionResource::collection(UserSubscription::query()->get()));
    }
}
