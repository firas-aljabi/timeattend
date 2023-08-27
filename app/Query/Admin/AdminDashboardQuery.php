<?php

namespace App\Query\Admin;

use App\Models\Attendance;
use App\Models\Nationalitie;
use App\Models\User;
use App\Statuses\EmployeeStatus;
use App\Statuses\UserTypes;
use Carbon\Carbon;

class AdminDashboardQuery
{
    public function getDashboardData(): object
    {

        $employeesCount = $this->getAllEmployees();
        $attendanceRate = $this->getAttendaneRate();
        $onDutyEmployeesCount = $this->getOnDutyEmployees();
        $onVacationEmployeesCount = $this->getOnVacationEmployees();
        $onDutyEmployeesPercentage = $this->getOnDutyEmployeesPercentage();
        $onVacationEmployeesPercentage = $this->getOnVacationEmployeesPercentage();
        $nationalitiesRate = $this->getNationalatiesRate();
        $contractExpirationPercentage = $this->getContractExpirationPercentage();
        $contractExpiration = $this->getContractExpiration();
        $expiredPassportsPercentage = $this->getExpiredPassportsPercentage();
        $expiredPassports = $this->getExpiredPassports();
        $maleEmployees = $this->getMaleEmployeesPercentage();
        $femaleEmployees = $this->getFemaleEmployeesPercentage();

        $result = [
            'all_employees_count' => $employeesCount,
            'attendance_rate' => $attendanceRate,
            'on_duty_employees_count' => $onDutyEmployeesCount,
            'on_vacation_employees_count' => $onVacationEmployeesCount,
            'on_duty_employees_percentage' => $onDutyEmployeesPercentage,
            'on_vacation_employees_percentage' => $onVacationEmployeesPercentage,
            'contract_expiration_percentage' => $contractExpirationPercentage,
            'nationalities_rate' => $nationalitiesRate,
            'contract_expiration' =>  $contractExpiration,
            'expired_passports_percentage' => $expiredPassportsPercentage,
            'expired_passports' => $expiredPassports,
            'male_employees' => $maleEmployees,
            'female_employees' => $femaleEmployees,
        ];
        return (object) $result;
    }

    private function getAllEmployees()
    {
        $allEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->count();
        return $allEmployeesCount;
    }

    private function getOnDutyEmployees()
    {
        $onDutyEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->where('status', EmployeeStatus::ON_DUTY)->count();
        return $onDutyEmployeesCount;
    }
    private function getOnDutyEmployeesPercentage()
    {
        $onDutyEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->where('status', EmployeeStatus::ON_DUTY)->count();
        $allEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->count();
        if ($allEmployeesCount != 0) {
            $onDutyEmployeePercentage = ($onDutyEmployeesCount / $allEmployeesCount) * 100;
            return number_format($onDutyEmployeePercentage);
        } else {
            return 0;
        }
    }
    private function getOnVacationEmployeesPercentage()
    {
        $onVacationEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->where('status', EmployeeStatus::ON_VACATION)->count();
        $allEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->count();

        if ($allEmployeesCount != 0) {
            $onVacationEmployeePercentage = ($onVacationEmployeesCount / $allEmployeesCount) * 100;
            return number_format($onVacationEmployeePercentage);
        } else {
            return 0;
        }
    }

    private function getMaleEmployeesPercentage()
    {
        $maleEmployeeCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->where('gender', 'male')->count();
        $allEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->count();

        if ($allEmployeesCount != 0) {
            $maleEmployeePercentage = ($maleEmployeeCount / $allEmployeesCount) * 100;
            return number_format($maleEmployeePercentage);
        } else {
            return 0;
        }
    }
    private function getFemaleEmployeesPercentage()
    {
        $femaleEmployeeCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->where('gender', 'female')->count();
        $allEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->count();

        if ($allEmployeesCount != 0) {
            $femaleEmployeePercentage = ($femaleEmployeeCount / $allEmployeesCount) * 100;
            return number_format($femaleEmployeePercentage);
        } else {
            return 0;
        }
    }

    private function getOnVacationEmployees()
    {
        $onVacationEmployeesCount = User::where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->where('status', EmployeeStatus::ON_VACATION)->count();
        return $onVacationEmployeesCount;
    }

    public function getAttendaneRate()
    {
        $currentDate = Carbon::now()->toDateString();

        $startDate = Carbon::now()->startOfMonth()->toDateString();

        $daysOfMonth = Carbon::parse($currentDate)->diffInDays(Carbon::parse($startDate)) + 1;

        $attendancesCount = Attendance::where('status', 1)->where('company_id', auth()->user()->company_id)->whereMonth('date', Carbon::now()->month)->count();

        if ($attendancesCount != 0) {
            $attendanceRate = ($attendancesCount / $daysOfMonth) * 100;
            return number_format($attendanceRate);
        } else {
            return 0;
        }
    }

    public function getNationalatiesRate()
    {
        $nationalityCounts = User::groupBy('nationalitie_id')
            ->where('type', UserTypes::EMPLOYEE)
            ->selectRaw('nationalitie_id, COUNT(*) as count')
            ->get()
            ->pluck('count', 'nationalitie_id');

        $totalEmployees = User::where('type', UserTypes::EMPLOYEE)
            ->where('company_id', auth()->user()->company_id)
            ->count();

        $nationalityWithMostEmployees = $nationalityCounts->map(function ($count, $nationalityId) use ($totalEmployees) {
            $nationality = Nationalitie::find($nationalityId);
            return [
                'nationality_id' => $nationalityId,
                'nationality_name' => $nationality->Name,
                'percent' => round(($count / $totalEmployees) * 100, 2)
            ];
        })
            ->sortByDesc('percent')
            ->take(4)
            ->values();

        return $nationalityWithMostEmployees;
    }


    public function getContractExpirationPercentage()
    {
        $totalEmployees = User::where('type', UserTypes::EMPLOYEE)->count();

        $approachingExpirationEmployees = User::where('type', UserTypes::EMPLOYEE)
            ->whereNotNull('end_job_contract')
            ->where('end_job_contract', '<=', Carbon::now()->addMonth())
            ->count();

        if ($approachingExpirationEmployees != 0) {
            $percentApproachingExpiration = round(($approachingExpirationEmployees / $totalEmployees) * 100, 2);
            return number_format($percentApproachingExpiration);
        } else {
            return 0;
        }
    }

    public function getExpiredPassportsPercentage()
    {
        $totalEmployees = User::where('type', UserTypes::EMPLOYEE)->count();

        $passportExpirationEmployees = User::where('type', UserTypes::EMPLOYEE)
            ->whereNotNull('end_passport')
            ->where('end_passport', '<=', Carbon::now()->addMonth())
            ->count();
        if ($passportExpirationEmployees != 0) {
            $percentExpirationPassport = round(($passportExpirationEmployees / $totalEmployees) * 100, 2);
            return number_format($percentExpirationPassport);
        } else {
            return 0;
        }
    }

    public function getContractExpiration()
    {
        $approachingExpirationEmployees = User::where('type', UserTypes::EMPLOYEE)
            ->whereNotNull('end_job_contract')
            ->where('end_job_contract', '<=', Carbon::now()->addMonth())
            ->get(['id', 'name', 'start_job_contract', 'end_job_contract']);
        return $approachingExpirationEmployees;
    }

    public function getExpiredPassports()
    {
        $approachingExpirationEmployees = User::where('type', UserTypes::EMPLOYEE)
            ->whereNotNull('end_passport')
            ->where('end_passport', '<=', Carbon::now()->addMonth())
            ->get(['id', 'name', 'end_passport']);
        return $approachingExpirationEmployees;
    }
}
