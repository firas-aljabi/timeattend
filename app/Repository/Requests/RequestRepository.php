<?php

namespace App\Repository\Requests;

use App\Filter\VacationRequests\RequestFilter;
use App\Http\Trait\UploadImage;
use App\Models\EmployeeAvailableTime;
use App\Models\Request;
use App\Repository\BaseRepositoryImplementation;
use App\Statuses\GenderStatus;
use App\Statuses\PaymentType;
use App\Statuses\RequestStatus;
use App\Statuses\RequestType;
use App\Statuses\UserTypes;
use App\Statuses\VacationRequestTypes;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestRepository extends BaseRepositoryImplementation
{
    use UploadImage;
    public function getFilterItems($filter)
    {
    }

    public function my_requests($filter)
    {
        $records = Request::query()->where('user_id', auth()->user()->id)->where('company_id', auth()->user()->company_id);
        if ($filter instanceof RequestFilter) {

            $records->when(isset($filter->type), function ($records) use ($filter) {
                $records->where('type', $filter->getRequestType());
            });

            $records->when(isset($filter->orderBy), function ($records) use ($filter) {
                $records->orderBy($filter->getOrderBy(), $filter->getOrder());
            });

            return ['success' => true, 'data' => $records->get()];
        }
        return ['success' => true, 'data' => $records->get()];
    }


    public function vacation_requests()
    {
        $records = Request::query()->where('type', RequestType::VACATION)->where('company_id', auth()->user()->company_id)->with('user');
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {

            return ['success' => true, 'data' => $records->get()];
        } else {

            return ['success' => false, 'message' => "Unauthorized"];
        }
    }
    public function justify_requests()
    {
        $records = Request::query()->where('type', RequestType::JUSTIFY)->where('company_id', auth()->user()->company_id)->with('user');
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {


            return ['success' => true, 'data' => $records->get()];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }
    public function retirement_requests()
    {
        $records = Request::query()->where('type', RequestType::RETIREMENT)->where('company_id', auth()->user()->company_id)->with('user');
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {

            return ['success' => true, 'data' => $records->get()];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }
    public function resignation_requests()
    {
        $records = Request::query()->where('type', RequestType::RESIGNATION)->where('company_id', auth()->user()->company_id)->with('user');
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {

            return ['success' => true, 'data' => $records->get()];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }

    public function add_vacation_request($data)
    {
        DB::beginTransaction();

        try {
            $user_id = Auth::id();
            $current_date = date('Y-m-d');
            $existing_request = Request::where('user_id', $user_id)
                ->where('type', RequestType::VACATION)
                ->whereDate('created_at', $current_date)
                ->first();
            if ($existing_request) {
                return ['success' => false, 'message' => "A Vacation request has already been created for this user today,Please Contact With Hr."];
            }
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];
            $start_time = $data['start_time'];
            $end_time = $data['end_time'];


            $availableTime = EmployeeAvailableTime::where('user_id', auth()->user()->id)->first();

            if (!$availableTime) {
                return ['success' => false, 'message' => "You Cannot Request Vacation Because You Don't Have Accrued Vacation Hours."];
            }
            $availableTimeHoursCount =  $availableTime->hours_daily;
            $availableDaysMonthlyCount =  $availableTime->days_monthly;
            $availableDaysAnnualCount =  $availableTime->days_annual;

            if ($data['vacation_type'] == VacationRequestTypes::HOURLY && $data['payment_type'] == PaymentType::PAYMENT) {
                $existing_vacation_time = Request::where('start_date', $start_date)
                    ->where('end_date', $end_date)
                    ->orWhere(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<=', $end_time)
                            ->where('end_time', '>=', $start_time);
                    })->first();
                if ($existing_vacation_time) {
                    return ['success' => false, 'message' => "You Cannot request a vacation in this Time, Please Choose Another Time."];
                }

                $start = new DateTime($start_time);
                $end = new DateTime($end_time);
                $diff = $start->diff($end);
                $hours = $diff->format('%h.%i');

                if ($availableTimeHoursCount != 0 && $hours <  $availableTimeHoursCount) {
                    $vacationRequest = new Request();
                    $vacationRequest->user_id = $user_id;
                    $vacationRequest->reason = $data['reason'];
                    $vacationRequest->type = RequestType::VACATION;
                    $vacationRequest->vacation_type = $data['vacation_type'];
                    $vacationRequest->status = RequestStatus::PENDING;
                    $vacationRequest->start_date = $data['start_date'];
                    $vacationRequest->end_date = $data['end_date'];
                    $vacationRequest->start_time = $data['start_time'];
                    $vacationRequest->end_time = $data['end_time'];
                    $vacationRequest->payment_type = $data['payment_type'];
                    $vacationRequest->company_id = auth()->user()->company_id;
                    $vacationRequest->save();

                    $availableTime->update([
                        'hours_daily' => $availableTime->hours_daily - $hours
                    ]);
                } else {
                    return ['success' => false, 'message' => "You Cannot request a vacation Because Your Available Hours Ended."];
                }
            } elseif ($data['vacation_type'] == VacationRequestTypes::DAILY && $data['payment_type'] == PaymentType::PAYMENT) {

                $existing_vacation_time = Request::where('start_date', $start_date)
                    ->where('type', RequestType::VACATION)
                    ->where('end_date', $end_date)
                    ->orWhere(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<=', $end_time)
                            ->where('end_time', '>=', $start_time);
                    })->first();
                if ($existing_vacation_time) {
                    return ['success' => false, 'message' => "You Cannot request a vacation in this Time, Please Choose Another Time."];
                }

                $start = new DateTime($start_date);
                $end = new DateTime($end_date);
                $diff = $start->diff($end);
                $days = $diff->days;

                if ($availableDaysMonthlyCount != 0 && $days <  $availableDaysMonthlyCount) {
                    $vacationRequest = new Request();
                    $vacationRequest->user_id = $user_id;
                    $vacationRequest->reason = $data['reason'];
                    $vacationRequest->type = RequestType::VACATION;
                    $vacationRequest->vacation_type = $data['vacation_type'];
                    $vacationRequest->status = RequestStatus::PENDING;
                    $vacationRequest->start_date = $data['start_date'];
                    $vacationRequest->end_date = $data['end_date'];
                    $vacationRequest->payment_type = $data['payment_type'];
                    $vacationRequest->company_id = auth()->user()->company_id;
                    $vacationRequest->save();

                    $availableTime->update([
                        'days_monthly' => $availableTime->days_monthly - $days
                    ]);
                } else {
                    return ['success' => false, 'message' => "You Cannot request a vacation Because Your Available Hours Ended."];
                }
            } elseif ($data['vacation_type'] == VacationRequestTypes::ANNUL && $data['payment_type'] == PaymentType::PAYMENT) {

                $existing_vacation_time = Request::where('type', RequestType::VACATION)
                    ->where('start_date', $start_date)
                    ->where('end_date', $end_date)
                    ->orWhere(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<=', $end_time)
                            ->where('end_time', '>=', $start_time);
                    })->first();
                if ($existing_vacation_time) {
                    return ['success' => false, 'message' => "You Cannot request a vacation in this Time, Please Choose Another Time."];
                }

                $start = new DateTime($start_date);
                $end = new DateTime($end_date);
                $diff = $start->diff($end);
                $daysAnnual = $diff->days;

                if ($availableDaysAnnualCount != 0 && $daysAnnual <  $availableDaysAnnualCount) {
                    $vacationRequest = new Request();
                    $vacationRequest->user_id = $user_id;
                    $vacationRequest->reason = $data['reason'];
                    $vacationRequest->type = RequestType::VACATION;
                    $vacationRequest->vacation_type = $data['vacation_type'];
                    $vacationRequest->status = RequestStatus::PENDING;
                    $vacationRequest->start_date = $data['start_date'];
                    $vacationRequest->end_date = $data['end_date'];
                    $vacationRequest->payment_type = $data['payment_type'];
                    $vacationRequest->company_id = auth()->user()->company_id;
                    $vacationRequest->save();

                    $availableTime->update([
                        'days_annual' => $availableTime->days_annual - $daysAnnual
                    ]);
                } else {
                    return ['success' => false, 'message' => "You Cannot request a vacation Because Your Available Hours Ended."];
                }
            } elseif ($data['vacation_type'] == VacationRequestTypes::METERNITY && auth()->user()->gender == GenderStatus::MALE) {
                return ['success' => false, 'message' => "You Cannot request a vacation For This Reason."];
            } else {
                $existing_vacation = Request::where('type', RequestType::VACATION)
                    ->where('start_date', $start_date)
                    ->orWhere('end_date', $end_date)
                    ->whereBetween('start_date', [$start_date, $end_date])
                    ->whereBetween('end_date', [$start_date, $end_date])
                    ->orWhere(function ($query) use ($start_date, $end_date) {
                        $query->where('start_date', '<=', $end_date)
                            ->where('end_date', '>=', $start_date);
                    })->first();

                $vacationRequest = new Request();
                $vacationRequest->user_id = $user_id;
                $vacationRequest->reason = $data['reason'];
                $vacationRequest->type = RequestType::VACATION;
                $vacationRequest->vacation_type = $data['vacation_type'];
                $vacationRequest->status = RequestStatus::PENDING;
                $vacationRequest->start_date = $data['start_date'];
                $vacationRequest->end_date = $data['end_date'];
                $vacationRequest->payment_type = $data['payment_type'];
                $vacationRequest->company_id = auth()->user()->company_id;

                $vacationRequest->save();
                if ($existing_vacation) {
                    return ['success' => false, 'message' => "You Cannot request a vacation in this Time, Please Choose Another Date."];
                }
            }
            DB::commit();
            if ($vacationRequest === null) {
                return ['success' => false, 'message' => "Vacation Request was not created"];
            }

            return ['success' => true, 'data' => $vacationRequest->load(['user'])];
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());

            throw $e;
        }
    }

    public function add_justify_request($data)
    {
        DB::beginTransaction();
        try {
            $user_id = Auth::id();
            if (isset($data['date'])) {
                $existing_request = Request::where('user_id', $user_id)->where('type', RequestType::JUSTIFY)
                    ->whereDate('date', $data['date'])
                    ->first();

                if ($existing_request) {
                    return ['success' => false, 'message' => "A justify request has already been created for this user today."];
                }
            }

            if (isset($data['start_date']) && isset($data['end_date'])) {
                $existing_request_multi_date = Request::where('user_id', $user_id)->where('type', RequestType::JUSTIFY)
                    ->orWhere('start_date', $data['start_date'])
                    ->orWhere('end_date', $data['end_date'])
                    ->first();
                if ($existing_request_multi_date) {
                    return ['success' => false, 'message' => "A justify request has already been created for this user today."];
                }
            }

            $justifyRequest = new Request();
            $justifyRequest->user_id =  $user_id;
            $justifyRequest->reason = $data['reason'];
            $justifyRequest->type = RequestType::JUSTIFY;
            $justifyRequest->justify_type = $data['justify_type'];

            $justifyRequest->status = RequestStatus::PENDING;
            $justifyRequest->company_id = auth()->user()->company_id;
            if (isset($data['date'])) {
                $justifyRequest->date = $data['date'];
            } else {
                $justifyRequest->start_date = $data['start_date'];
                $justifyRequest->end_date = $data['end_date'];
            }

            if (Arr::has($data, 'attachments')) {
                $file = Arr::get($data, 'attachments');
                $file_name = $this->uploadEmployeeRequestsAttachment($file,  $justifyRequest->user_id);
                $justifyRequest->attachments = $file_name;
            }

            $justifyRequest->save();

            DB::commit();

            if ($justifyRequest === null) {
                return ['success' => false, 'message' => "Justify Request was not created"];
            }

            return ['success' => true, 'data' => $justifyRequest->load(['user'])];
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());

            throw $e;
        }
    }

    public function add_retirement_request($data)
    {
        DB::beginTransaction();
        try {

            if (auth()->user()->type == UserTypes::HR || auth()->user()->type == UserTypes::EMPLOYEE) {
                $user_id = Auth::id();
                $existing_request = Request::where('user_id', $user_id)->where('type', RequestType::RETIREMENT)->first();
                if ($existing_request) {
                    return ['success' => false, 'message' => "A Retirement request has already been created for this user."];
                }
                $employeeRequest = new Request();
                $employeeRequest->user_id =  auth()->user()->id;
                $employeeRequest->reason = $data['reason'];
                $employeeRequest->type = RequestType::RETIREMENT;
                $employeeRequest->status = RequestStatus::PENDING;
                $employeeRequest->date = date('Y-m-d');
                $employeeRequest->company_id = auth()->user()->company_id;
                if (Arr::has($data, 'attachments')) {
                    $file = Arr::get($data, 'attachments');
                    $file_name = $this->uploadEmployeeRequestsAttachment($file,  $employeeRequest->user_id);
                    $employeeRequest->attachments = $file_name;
                }
                $employeeRequest->save();
                DB::commit();
                if ($employeeRequest === null) {
                    return ['success' => false, 'message' => "Retirement Request was not created"];
                }
                return ['success' => true, 'data' => $employeeRequest->load(['user'])];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            throw $e;
        }
    }

    public function add_resignation_request($data)
    {
        DB::beginTransaction();
        try {
            if (auth()->user()->type == UserTypes::HR || auth()->user()->type == UserTypes::EMPLOYEE) {
                $user_id = Auth::id();
                $existing_request = Request::where('user_id', $user_id)->where('type', RequestType::RESIGNATION)->first();
                if ($existing_request) {
                    return ['success' => false, 'message' => "A Resignation request has already been created for this user."];
                }
                $employeeRequest = new Request();
                $employeeRequest->user_id =  auth()->user()->id;
                $employeeRequest->reason = $data['reason'];
                $employeeRequest->type = RequestType::RESIGNATION;
                $employeeRequest->status = RequestStatus::PENDING;
                $employeeRequest->date = date('Y-m-d');
                $employeeRequest->company_id = auth()->user()->company_id;
                if (Arr::has($data, 'attachments')) {
                    $file = Arr::get($data, 'attachments');
                    $file_name = $this->uploadEmployeeRequestsAttachment($file,  $employeeRequest->user_id);
                    $employeeRequest->attachments = $file_name;
                }
                $employeeRequest->save();
                DB::commit();
                if ($employeeRequest === null) {
                    return ['success' => false, 'message' => "Resignation Request was not created"];
                }
                return ['success' => true, 'data' => $employeeRequest->load(['user'])];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            throw $e;
        }
    }

    public function reject_request($data)
    {
        DB::beginTransaction();
        try {
            $request = $this->getById($data['request_id']);
            if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type = UserTypes::HR && auth()->user()->company_id == $request->company_id) {

                $request->update([
                    'status' => RequestStatus::REJECTED,
                    'reject_reason' => $data['reject_reason']
                ]);

                DB::commit();
                if ($request === null) {
                    return ['success' => false, 'message' => "Vacation Request was not Updated"];
                }
                return ['success' => true, 'data' => $request->load('user')];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function model()
    {
        return Request::class;
    }
}
