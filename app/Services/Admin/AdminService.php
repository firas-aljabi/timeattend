<?php

namespace App\Services\Admin;

use App\Filter\Attendance\AttendanceFilter;
use App\Filter\Contract\ContractFilter;
use App\Filter\Employees\EmployeeFilter;
use App\Filter\Nationalalities\NationalFilter;
use App\Filter\Salary\SalaryFilter;
use App\Interfaces\Admin\AdminServiceInterface;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Salary;
use App\Models\User;
use App\Query\Admin\AdminDashboardQuery;
use App\Repository\Admin\AdminRepository;
use App\Statuses\UserTypes;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminService implements AdminServiceInterface
{


    public function __construct(private AdminRepository $adminRepository, private AdminDashboardQuery $adminDashboardQuery)
    {
    }

    public function create_employee($data)
    {
        return $this->adminRepository->create_employee($data);
    }
    public function update_employee($data)
    {
        return $this->adminRepository->update_employee($data);
    }
    public function update_employee_permission_time($data)
    {
        return $this->adminRepository->update_employee_permission_time($data);
    }


    public function create_hr($data)
    {
        return $this->adminRepository->create_hr($data);
    }

    public function create_admin($data)
    {
        return $this->adminRepository->create_admin($data);
    }

    public function check_in_attendance($data)
    {
        return $this->adminRepository->check_in_attendance($data);
    }

    public function check_out_attendance($data)
    {
        return $this->adminRepository->check_out_attendance($data);
    }


    public function determine_working_hours($data)
    {
        return $this->adminRepository->determine_working_hours($data);
    }
    public function update_working_hours($data)
    {
        return $this->adminRepository->update_working_hours($data);
    }


    public function reward_adversaries_allowance_salary($data)
    {
        return $this->adminRepository->reward_adversaries_allowance_salary($data);
    }

    public function update_salary($data)
    {
        return $this->adminRepository->update_salary($data);
    }

    public function update_employment_contract($data)
    {
        return $this->adminRepository->update_employment_contract($data);
    }

    public function termination_employees_contract($data)
    {
        return $this->adminRepository->termination_employees_contract($data);
    }


    public function deleteEmployee(int $id)
    {
        if (auth()->user()->type == UserTypes::ADMIN) {
            return  User::where('id', $id)->forceDelete();
        } else {
            return "Unauthorized";
        }
    }

    public function getDashboardData()
    {
        return $this->adminDashboardQuery->getDashboardData();
    }
    public function getHrsList()
    {
        return $this->adminRepository->getHrsList();
    }


    public function getEmployees(EmployeeFilter $employeeFilter = null)
    {
        if ($employeeFilter != null)
            return $this->adminRepository->getFilterItems($employeeFilter);
        else
            return $this->adminRepository->paginate();
    }

    public function employees_salaries(SalaryFilter $salaryFilter = null)
    {
        if ($salaryFilter != null)
            return $this->adminRepository->getSalaryFilterItems($salaryFilter);
        else
            return $this->adminRepository->paginate();
    }

    public function employees_attendances(AttendanceFilter $attendanceFilter = null)
    {
        if ($attendanceFilter != null)
            return $this->adminRepository->employees_attendances($attendanceFilter);
        else
            return $this->adminRepository->paginate();
    }

    public function get_contract_expiration(ContractFilter $contractFilter = null)
    {
        if ($contractFilter != null)
            return $this->adminRepository->get_contract_expiration($contractFilter);
        else
            return $this->adminRepository->paginate();
    }


    public function list_of_nationalities(NationalFilter $nationalFilter = null)
    {
        if ($nationalFilter != null)
            return $this->adminRepository->list_of_nationalities($nationalFilter);
        else
            return $this->adminRepository->paginate();
    }



    public function showEmployee(int $id)
    {
        return $this->adminRepository->getById($id)->load(['salaries', 'availableTime', 'vacationRequestsApproved', 'attendancesMonthly', 'nationalitie', 'deposits', 'shifts']);
    }


    public static function careteAttendance()
    {

        $today = Carbon::today()->format('Y-m-d');
        $userId = Auth::id();

        $existingAttendance = Attendance::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->exists();

        // If an attendance record does not exist, create a new one
        if (!$existingAttendance) {
            Attendance::create([
                'user_id' => $userId,
                'date' => $today,
                'status' => 1
            ]);
        }
    }

    public static function AttendancePercentage($id)
    {

        $startDate = date('Y-m-01');

        $endDate = date('Y-m-d');

        $totalDays = date_diff(date_create($startDate), date_create($endDate))->format('%a');

        $attendanceDays = DB::table('attendances')
            ->where('user_id', $id)
            ->where('status', 1)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();
        if ($totalDays != 0) {
            $percentage = ($attendanceDays / $totalDays) * 100;
            return number_format($percentage);
        } else {
            return 0;
        }
    }


    public function profile()
    {
        return $this->adminRepository->profile();
    }

    public static function CalculateNumberOfWorkingHours($id)
    {
        $user = User::find($id);
        $user_shifts = $user->shifts;
        $total_working_hours = 0;

        if ($user_shifts != null) {
            foreach ($user_shifts as $shift) {
                $start_time = Carbon::parse($shift->start_time);
                $end_time = Carbon::parse($shift->end_time);
                $working_hours = $end_time->diffInHours($start_time);
                $total_working_hours += $working_hours;

                $start_break_hour = Carbon::parse($shift->start_break_hour);
                $end_break_hour = Carbon::parse($shift->end_break_hour);
                $break_hours = $end_break_hour->diffInHours($start_break_hour);
                $total_working_hours -= $break_hours;
            }
        }

        return ceil($total_working_hours);
    }
}
