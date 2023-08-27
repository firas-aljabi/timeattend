<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Deposit\DepositResource;
use App\Http\Resources\Employees\AttendanceResource;
use App\Http\Resources\Employees\EmployeeAvailableTimeResource;
use App\Http\Resources\Employees\SalaryResource;
use App\Http\Resources\Requests\RequestResource;
use App\Http\Resources\Requests\VacationResource;
use App\Http\Resources\Shifts\ShiftRersource;
use App\Http\Resources\Shifts\ShiftRrsource;
use App\Models\EmployeeAvailableTime;
use App\Services\Admin\AdminService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceResponse;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user_id = $this->id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'work_email' => $this->work_email,
            'status' => $this->status,
            'type' => $this->type,
            'gender' => $this->gender,
            'mobile' => $this->mobile,
            'phone' => $this->phone,
            'departement' => $this->departement,
            'address' => $this->address,
            'position' => $this->position,
            'skills' => $this->skills,
            'serial_number' => $this->serial_number,
            'birthday_date' => $this->birthday_date,
            'marital_status' => $this->marital_status,
            'guarantor' => $this->guarantor,
            'branch' => $this->branch,
            'start_job_contract' => $this->start_job_contract,
            'end_job_contract' => $this->end_job_contract,
            'end_visa' => $this->end_visa,
            'end_passport' => $this->end_passport,
            'end_employee_sponsorship' => $this->end_employee_sponsorship,
            'end_municipal_card' => $this->end_municipal_card,
            'end_health_insurance' => $this->end_health_insurance,
            'end_employee_residence' => $this->end_employee_residence,
            'image' => $this->image ? asset($this->image) : null,
            'id_photo' => $this->id_photo ? asset($this->id_photo) : null,
            'biography' => $this->biography ? asset($this->biography) : null,
            'employee_sponsorship' => $this->employee_sponsorship ? asset($this->employee_sponsorship) : null,
            'visa' => $this->visa ? asset($this->visa) : null,
            'passport' => $this->passport ? asset($this->passport) : null,
            'municipal_card' => $this->municipal_card ? asset($this->municipal_card) : null,
            'health_insurance' => $this->health_insurance ? asset($this->health_insurance) : null,
            'employee_residence' => $this->employee_residence ? asset($this->employee_residence) : null,
            'permission_to_entry' => $this->permission_to_entry,
            'entry_time' => $this->entry_time,
            'permission_to_leave' => $this->permission_to_leave,
            'leave_time' => $this->leave_time,
            'nationalitie' => $this->whenLoaded('nationalitie', function () {
                return [
                    'name' => $this->nationalitie->Name,
                ];
            }),
            'percentage' => AdminService::AttendancePercentage($user_id),
            'basic_salary' => floatVal($this->basic_salary),
            'salaries' => SalaryResource::collection($this->whenLoaded('salaries')),
            'availableTime' => $this->whenLoaded('availableTime', function () {
                return [
                    'hours_daily' => intval($this->availableTime->hours_daily),
                    'hours_annual' => intval($this->availableTime->days_annual),
                ];
            }),
            'vacation_approved' => RequestResource::collection($this->whenLoaded('vacationRequestsApproved')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendancesMonthly')),
            'shifts' => ShiftRersource::collection($this->whenLoaded('shifts')),
            'deposits' => DepositResource::collection($this->whenLoaded('deposits')),
        ];
    }
}