<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;;

use App\Services\Admin\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;


/**
 * @group Authentication
 *
 * APIs for managing Authentication
 */
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    /**
     * Login
     *
     * This endpoint is used to login a user to the system.
     *
     * @bodyParam email string required Example: mouaz@gmail.com
     * @bodyParam password string required Example: 0123456789
     *
     *
     * @response  {
     * "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjkzMDM2MzM5LCJleHAiOjE2OTMwMzk5MzksIm5iZiI6MTY5MzAzNjMzOSwianRpIjoic0JtVWZMcVdiTjNBeVVQUCIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.LxUrVJ_gdDor8mju1o5Db43RM1c0yLlEYDpVV0RwdH8",
     * "user": {
     * "id": 2,
     * "name": "Firas Jabi",
     * "email": "firassaljabi1232@gmail.com",
     * "work_email": "firassaljabi1237@goma.com",
     * "email_verified_at": null,
     * "mobile": "0969040342",
     * "phone": "0935463111",
     * "serial_number": "00011",
     * "nationalitie_id": 2,
     * "company_id": 1,
     * "birthday_date": "1998-11-26",
     * "material_status": 2,
     * "gender": 1,
     * "address": "Damascus",
     * "guarantor": null,
     * "branch": "syria branch",
     * "departement": null,
     * "position": null,
     * "type": 2,
     * "status": 1,
     * "skills": null,
     * "start_job_contract": null,
     * "end_job_contract": null,
     * "image": null,
     * "id_photo": null,
     * "biography": null,
     * "employee_sponsorship": null,
     * "end_employee_sponsorship": null,
     * "employee_residence": null,
     * "end_employee_residence": null,
     * "visa": null,
     * "end_visa": null,
     * "passport": null,
     * "end_passport": null,
     * "municipal_card": null,
     * "end_municipal_card": null,
     * "health_insurance": null,
     * "end_health_insurance": null,
     * "basic_salary": "0.00",
     * "permission_to_entry": 0,
     * "entry_time": null,
     * "permission_to_leave": 0,
     * "leave_time": null,
     * "code": null,
     * "expired_at": null,
     * "created_at": "2023-08-26T07:01:20.000000Z",
     * "updated_at": "2023-08-26T07:01:20.000000Z",
     * "deleted_at": null
     * }
     * }
     *
     * @response 401 scenario="Failed Login"{
     * "message": "Invalid login credentials"
     * }
     *
     */

    public function login(Request $request)
    {

        try {
            if ($request->query()) {
                return response()->json(['message' => 'Query parameters are not allowed']);
            } else {
                $user = User::where('email', $request->email)->first();
                // if ($user->code != null) {
                //     return response()->json(['message' => 'You Should Verfiy Your Code..!']);
                // }
                $rules = [
                    "email" => "required|email|max:255|regex:/^[a-zA-Z0-9._%+-]{1,16}[^*]{0,}@[^*]+$/",
                    "password" => "required|min:8|max:24|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,24}$/",
                ];
                $validator = Validator::make($request->only(['email', 'password']), $rules);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }

                $credentials = $request->only(['email', 'password']);

                $token = Auth::guard('api')->attempt($credentials);


                if (!$token)
                    return response()->json(['error' => 'Invalid login credentials'], 401);

                $user = Auth::guard('api')->user();


                return response()->json(['token' => $token, 'user' => $user]);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], $ex->getCode());
        }
    }


    public function verify_code(Request $request)
    {
        $user = User::where('code', $request->code)->first();
        if ($request->code == $user->code) {
            $user->reset_code();
            return response()->json(['message' => 'Success Verfiy Code..!!']);
        }
    }


    /**
     * Logout
     *
     * This endpoint is used to log out a user from the system.
     * @authenticated
     * @response {
     * "message": "User successfully signed out"
     * }
     *
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
}