<?php

namespace App\Http\Controllers\Api;

use App\ApiHelper\ApiResponseHelper;
use App\ApiHelper\ErrorResult;
use App\ApiHelper\Result;
use App\ApiHelper\SuccessResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\TerminateContractRequest;
use App\Http\Requests\Employees\AdminUpdateEmployeeRequest;
use App\Http\Requests\Employees\CheckInAttendanceRequest;
use App\Http\Requests\Employees\CheckOutAttendanceRequest;
use App\Http\Requests\Employees\CreateAdminRequest;
use App\Http\Requests\Employees\CreateEmployeeRequest;
use App\Http\Requests\Employees\DetermineWorkingHoursRequest;
use App\Http\Requests\Employees\GetContractExpirationList;
use App\Http\Requests\Employees\GetEmployeesAttendancesListRequest;
use App\Http\Requests\Employees\GetEmployeesListRequest;
use App\Http\Requests\Employees\GetEmployeesSalariesListRequest;
use App\Http\Requests\Employees\RewerdsAdversriesSalaryRequest;
use App\Http\Requests\Employees\UpdateEmployeeContractRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use App\Http\Requests\Employees\UpdateEmployeeShift;
use App\Http\Requests\Employees\UpdateSalaryRequest;
use App\Http\Requests\Nationalitie\GetNationalitiesRequest;
use App\Http\Resources\Admin\DashboardDataResource;
use App\Http\Resources\Admin\EmployeeResource;
use App\Http\Resources\Contract\ContractResource;
use App\Http\Resources\Contract\UserContractResource;
use App\Http\Resources\Employees\AttendanceResource;
use App\Http\Resources\Employees\EmployeeAvailableTimeResource;
use App\Http\Resources\Employees\SalaryResource;
use App\Http\Resources\Nationalitie\NationalitiesRrsource;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Shifts\ShiftRersource;
use App\Services\Admin\AdminService;
use Illuminate\Http\Request;


/**
 * @group Admin Operations
 *
 * @authenticated
 *
 * APIs for managing Admin Operations
 */
class AdminController extends Controller
{

    public function __construct(private AdminService $adminService)
    {
    }
    /**
     * Add Employee to the Company
     *
     * This endpoint is used to Add Employee to the Company and Just Admin Or Hr Can Access To This Api.
     *
     * @bodyParam name string required The name of the employee must be required Example:mouaz alkhateeb.
     *
     * @bodyParam email string required The email of the employee, The email must have a maximum length of 255 characters. It should follow the pattern of having 16 letters before the @ symbol.
     *                                 Example: mouaz@gmail.com
     *
     * @bodyParam password string required The password of the employee. Must not be greater than 24 characters and should contain both numbers and characters. Example: Abc071096904038
     *
     * @bodyParam work_email email The email work of the employee, The work_email must have a maximum length of 255 characters. It should follow the pattern of having 16 letters before the @ symbol. Example:mouazalkhateeb@goma.com.
     *
     * @bodyParam image file Must not be greater than 2048 kilobytes
     *
     * @bodyParam id_photo file Must be pdf and not be greater than 2048 kilobytes
     *
     * @bodyParam biography file  Must not be greater than 2048 kilobytes
     *
     * @bodyParam visa file Must not be greater than 2048 kilobytes
     *
     * @bodyParam municipal_card file Must not be greater than 2048 kilobytes
     *
     * @bodyParam health_insurance file Must not be greater than 2048 kilobytes
     *
     * @bodyParam passport  file Must not be greater than 2048 kilobytes
     * @bodyParam serial_number number required Custom Example: 0101122
     * @bodyParam birthday_date date Custom Example: 2023-08-27
     * @bodyParam nationalitie_id int required Must be from 1 to 56
     * @bodyParam material_status int optional The material status of the user. Must be one of the following values:
     * - `1`: single status.
     * - `2`: married status.
     * - `3`: divorced status. Custom Example: 1
     *
     * @bodyParam gender int optional The gender of the user. Must be one of the following values:
     * - `1`: male.
     * - `2`: female. Custom Example: 1
     *
     * @bodyParam departement string Custom Example: It
     *
     * @bodyParam position  string Custom Example: Developer
     *
     * @bodyParam address  string Custom Example: Damascus
     *
     * @bodyParam guarantor  string Custom Example: Mouaz Alkhateeb
     *
     * @bodyParam branch  string Custom Example: Syria
     *
     * @bodyParam skills  string Custom Example: Drwaing
     *
     * @bodyParam status int optional The status of the user. Must be one of the following values:
     * - `1`: on duty.
     * - `2`: on vacation. Custom Example: 1
     *
     * @bodyParam start_job_contract date required Custom Example: 2023-08-27
     * @bodyParam end_job_contract date required Custom Example: 2023-08-27
     * @bodyParam end_visa date Custom Example: 2023-08-27
     * @bodyParam end_employee_sponsorship date Custom Example: 2023-08-27
     * @bodyParam end_municipal_card date Custom Example: 2023-08-27
     * @bodyParam end_health_insurance date Custom Example: 2023-08-27
     * @bodyParam end_employee_residence date Custom Example: 2023-08-27
     *
     * @bodyParam permission_to_leave int optional The permission to leave of the user. Must be one of the following values:
     * - `1`: True.
     * - `2`: False. Custom Example: 1
     *
     * @bodyParam leave_time number require if permission_to_leave equal 1. Must be one of the following values:
     * - 30.
     * - 60.
     * - 90  Custom Example: 60
     *
     * @bodyParam permission_to_entry  int optional The permission to entry of the user. Must be one of the following values:
     * - `1`: True.
     * - `2`: False. Custom Example: 1
     *
     * @bodyParam entry_time number require if permission_to_entry equal 1. Must be one of the following values:
     * - 30.
     * - 60.
     * - 90  Custom Example: 90
     *
     * @bodyParam basic_salary double required  Custom Example: 250000
     *
     * @bodyParam number_of_shifts int Must be at least 1 Custom Example: 3
     *
     *
     * @bodyParam shifts array optional An array of shift for the user. Required when `number_of_shifts` is not null.
     * @bodyParam user_id int required optional The ID of the user That Exists in users Table.
     * @bodyParam start_time time required The start time of the shift in the format `HH:MM:SS` Custom Example: 09:00:00.
     * @bodyParam end_time time required The end time of the shift in the format `HH:MM:SS` Custom Example: 09:00:00.
     * @bodyParam start_break_hour time required The start time of the break in the format `HH:MM:SS` Custom Example: 09:00:00.
     * @bodyParam end_break_hour time required The end time of the break in the format `HH:MM:SS` Custom Example: 09:00:00.

     *@response 201 scenario="Add a Employee"{
     * "data": {
     * "id": 4,
     * "name": "mouaz alkhateeb",
     * "email": "mouaz@gmail.com",
     * "work_email": "mouazalkhateeb@gmail.com",
     * "status": "1",
     * "type": 4,
     * "gender": "1",
     * "mobile": "0969040322",
     * "phone": "0969040322",
     * "departement": "it",
     * "address": "Damascus",
     * "position": null,
     * "skills": "no skills",
     * "serial_number": "000007",
     * "birthday_date": "2022-11-26",
     * "marital_status": null,
     * "guarantor": "admin",
     *"branch": "syria branch",
     * "start_job_contract": "2023-08-01",
     * "end_job_contract": "2023-10-01",
     * "end_visa": "2023-09-11",
     * "end_passport": "2023-09-11",
     * "end_employee_sponsorship": null,
     * "end_municipal_card": "2023-09-10",
     * "end_health_insurance": "2023-09-14",
     * "end_employee_residence": "2023-09-20",
     * "image": "http://127.0.0.1:8000/employees/2023-08-27-Employee-8.jpg",
     *"id_photo": null,
     * "biography": "http://127.0.0.1:8000/employees/2023-08-27-Employee-test.pdf",
     * "employee_sponsorship": "http://127.0.0.1:8000/employees/2023-08-27-Employee-55.jpg",
     * "visa": "http://127.0.0.1:8000/employees/2023-08-27-Employee-7.jpg",
     *"passport": "http://127.0.0.1:8000/employees/2023-08-27-Employee-6.jpeg",
     * "municipal_card": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpeg",
     * "health_insurance": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpg",
     * "employee_residence": "http://127.0.0.1:8000/employees/2023-08-27-Employee-77.jpg",
     * "permission_to_entry": "1",
     * "entry_time": "30",
     *"permission_to_leave": "1",
     *"leave_time": "60",
     *"nationalitie": {
     *   "name": "Syrian"
     * },
     *"percentage": "0",
     * "basic_salary": 200000,
     * "shifts": [
     *   {
     *      "id": 3,
     *     "start_time": "09:00:00",
     *    "end_time": "15:00:00",
     *   "start_break_hour": "12:30:00",
     *   "end_break_hour": "13:00:00"
     *},
     *{
     *   "id": 4,
     *   "start_time": "16:00:00",
     *  "end_time": "22:00:00",
     *  "start_break_hour": "20:30:00",
     *  "end_break_hour": "21:00:00"
     *}
     * ],
     * "deposits": []
     * }
     *}
     */
    public function store(CreateEmployeeRequest $request)
    {

        $createdData =  $this->adminService->create_employee($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = EmployeeResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    /**
     * Add Hr to the Company
     *
     * This endpoint is used to Add Hr to the Company and Just Admin Can Access To This Api.
     *
     * @bodyParam name string required The name of the employee must be required Example:mouaz alkhateeb.
     *
     * @bodyParam email string required The email of the employee, The email must have a maximum length of 255 characters. It should follow the pattern of having 16 letters before the @ symbol.
     *                                 Example: mouaz@gmail.com
     *
     * @bodyParam password string required The password of the employee. Must not be greater than 24 characters and should contain both numbers and characters. Example: Abc071096904038
     *
     * @bodyParam work_email email The email work of the employee, The work_email must have a maximum length of 255 characters. It should follow the pattern of having 16 letters before the @ symbol. Example:mouazalkhateeb@goma.com.
     *
     * @bodyParam image file Must not be greater than 2048 kilobytes
     *
     * @bodyParam id_photo file Must be pdf and not be greater than 2048 kilobytes
     *
     * @bodyParam biography file  Must not be greater than 2048 kilobytes
     *
     * @bodyParam visa file Must not be greater than 2048 kilobytes
     *
     * @bodyParam municipal_card file Must not be greater than 2048 kilobytes
     *
     * @bodyParam health_insurance file Must not be greater than 2048 kilobytes
     *
     * @bodyParam passport  file Must not be greater than 2048 kilobytes
     * @bodyParam serial_number number required Custom Example: 0101122
     * @bodyParam birthday_date date Custom Example: 2023-08-27
     * @bodyParam nationalitie_id int required Must be from 1 to 56
     * @bodyParam material_status int optional The material status of the user. Must be one of the following values:
     * - `1`: single status.
     * - `2`: married status.
     * - `3`: divorced status. Custom Example: 1
     *
     * @bodyParam gender int optional The gender of the user. Must be one of the following values:
     * - `1`: male.
     * - `2`: female. Custom Example: 1
     *
     * @bodyParam departement string Custom Example: It
     *
     * @bodyParam position  string Custom Example: Developer
     *
     * @bodyParam address  string Custom Example: Damascus
     *
     * @bodyParam guarantor  string Custom Example: Mouaz Alkhateeb
     *
     * @bodyParam branch  string Custom Example: Syria
     *
     * @bodyParam skills  string Custom Example: Drwaing
     *
     * @bodyParam status int optional The status of the user. Must be one of the following values:
     * - `1`: on duty.
     * - `2`: on vacation. Custom Example: 1
     *
     * @bodyParam start_job_contract date required Custom Example: 2023-08-27
     * @bodyParam end_job_contract date required Custom Example: 2023-08-27
     * @bodyParam end_visa date Custom Example: 2023-08-27
     * @bodyParam end_employee_sponsorship date Custom Example: 2023-08-27
     * @bodyParam end_municipal_card date Custom Example: 2023-08-27
     * @bodyParam end_health_insurance date Custom Example: 2023-08-27
     * @bodyParam end_employee_residence date Custom Example: 2023-08-27
     *
     *
     * @bodyParam basic_salary double required  Custom Example: 250000
     *
     *@response 201 scenario="Add a Hr"{
     * "data": {
     *"id": 5,
     *"name": "Hamza Fawaz",
     *"email": "hamzafawaz123@gmail.com",
     * "work_email": "hamzafawaz122@gmail.com",
     *"status": "1",
     *"type": 3,
     *"gender": "1",
     *"mobile": "0969010781",
     *"phone": "0935461184",
     *"departement": "it",
     *"address": "Damascus",
     *"position": null,
     *"skills": "no skills",
     *"serial_number": "000003",
     *"birthday_date": "1998-11-26",
     * "marital_status": null,
     *"guarantor": "admin",
     * "branch": "syria branch",
     *"start_job_contract": "2023-06-01",
     *"end_job_contract": "2023-09-01",
     *"end_visa": null,
     *"end_passport": null,
     *"end_employee_sponsorship": null,
     *"end_municipal_card": null,
     *"end_health_insurance": null,
     *"end_employee_residence": null,
     *"image": null,
     *"id_photo": null,
     *"biography": null,
     *"employee_sponsorship": null,
     *"visa": null,
     *"passport": null,
     *"municipal_card": null,
     *"health_insurance": null,
     *"employee_residence": null,
     *"permission_to_entry": null,
     * "entry_time": null,
     *"permission_to_leave": null,
     *"leave_time": null,
     *"percentage": "0",
     *"basic_salary": 200000
     *}
     */

    public function store_hr(CreateEmployeeRequest $request)
    {
        $createdData =  $this->adminService->create_hr($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = EmployeeResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Add Admin to the System
     *
     * This endpoint is used to Add Admin to the System and Just Super Admin Can Access To This Api.
     *
     * @bodyParam name string required The name of the admin must be required Example:Firas Aljabi.
     *
     * @bodyParam email string required The email of the admin, The email must have a maximum length of 255 characters. It should follow the pattern of having 16 letters before the @ symbol.
     *                                 Example: mouaz@gmail.com
     *
     * @bodyParam password string required The password of the admin. Must not be greater than 24 characters and should contain both numbers and characters. Example: Abc071096904038
     *
     * @bodyParam work_email email The email work of the employee, The work_email must have a maximum length of 255 characters. It should follow the pattern of having 16 letters before the @ symbol. Example:mouazalkhateeb@goma.com.
     *
     * @bodyParam image file Must not be greater than 2048 kilobytes
     * @bodyParam serial_number number required Custom Example: 0101122
     * @bodyParam birthday_date date Custom Example: 2023-08-27
     * @bodyParam nationalitie_id int required Must be from 1 to 56
     * @bodyParam comapny_id int required Must be exists in companies table.
     *
     * @bodyParam gender int optional The gender of the admin. Must be one of the following values:
     * - `1`: male.
     * - `2`: female. Custom Example: 1
     *
     * @bodyParam address  string Custom Example: Damascus
     *
     * @bodyParam branch  string Custom Example: Syria
     *
     * @response 201 scenario="Add a Admin"{
     *  "data": {
     *     "id": 6,
     *     "name": "Firas Jabi",
     *     "email": "firassaljabi1232@gmail.com",
     *    "work_email": "firassaljabi1237@goma.com",
     *     "status": null,
     *     "type": 2,
     *     "gender": "1",
     *     "mobile": "0969040342",
     *     "phone": "0935463111",
     *     "departement": null,
     *     "address": "Damascus",
     *    "position": null,
     *     "skills": null,
     *    "serial_number": "00011",
     *     "birthday_date": "1998-11-26",
     *    "marital_status": null,
     *    "guarantor": null,
     *     "branch": "syria branch",
     *     "start_job_contract": null,
     *     "end_job_contract": null,
     *     "end_visa": null,
     *     "end_passport": null,
     *     "end_employee_sponsorship": null,
     *     "end_municipal_card": null,
     *     "end_health_insurance": null,
     *     "end_employee_residence": null,
     *     "image": null,
     *     "id_photo": null,
     *     "biography": null,
     *     "employee_sponsorship": null,
     *     "visa": null,
     *     "passport": null,
     *     "municipal_card": null,
     *     "health_insurance": null,
     *     "employee_residence": null,
     *     "permission_to_entry": null,
     *     "entry_time": null,
     *     "permission_to_leave": null,
     *     "leave_time": null,
     *     "percentage": "0",
     *     "basic_salary": 0
     * }
     * }
     */

    public function store_admin(CreateAdminRequest $request)
    {
        $createdData =  $this->adminService->create_admin($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = EmployeeResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Updated Employee in the Company
     *
     * This endpoint is used to Update Employee in the Company and Just Same Employee Can Access To This Api.
     *
     * @bodyParam email string The email of the employee, The email must have a maximum length of 255 characters. It should follow the pattern of having 16 letters before the @ symbol.
     *                                 Example: mouaz@gmail.com
     *
     * @bodyParam image file Must not be greater than 2048 kilobytes
     *
     * @bodyParam address  string Custom Example: Damascus
     *
     *@response 201 scenario="Add a Employee"{
     * "data": {
     * "id": 4,
     * "name": "mouaz alkhateeb",
     * "email": "mouaz@gmail.com",
     * "work_email": "mouazalkhateeb@gmail.com",
     * "status": "1",
     * "type": 4,
     * "gender": "1",
     * "mobile": "0969040322",
     * "phone": "0969040322",
     * "departement": "it",
     * "address": "Damascus",
     * "position": null,
     * "skills": "no skills",
     * "serial_number": "000007",
     * "birthday_date": "2022-11-26",
     * "marital_status": null,
     * "guarantor": "admin",
     *"branch": "syria branch",
     * "start_job_contract": "2023-08-01",
     * "end_job_contract": "2023-10-01",
     * "end_visa": "2023-09-11",
     * "end_passport": "2023-09-11",
     * "end_employee_sponsorship": null,
     * "end_municipal_card": "2023-09-10",
     * "end_health_insurance": "2023-09-14",
     * "end_employee_residence": "2023-09-20",
     * "image": "http://127.0.0.1:8000/employees/2023-08-27-Employee-8.jpg",
     *"id_photo": null,
     * "biography": "http://127.0.0.1:8000/employees/2023-08-27-Employee-test.pdf",
     * "employee_sponsorship": "http://127.0.0.1:8000/employees/2023-08-27-Employee-55.jpg",
     * "visa": "http://127.0.0.1:8000/employees/2023-08-27-Employee-7.jpg",
     *"passport": "http://127.0.0.1:8000/employees/2023-08-27-Employee-6.jpeg",
     * "municipal_card": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpeg",
     * "health_insurance": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpg",
     * "employee_residence": "http://127.0.0.1:8000/employees/2023-08-27-Employee-77.jpg",
     * "permission_to_entry": "1",
     * "entry_time": "30",
     *"permission_to_leave": "1",
     *"leave_time": "60",
     *"nationalitie": {
     *   "name": "Syrian"
     * },
     *"percentage": "0",
     * "basic_salary": 200000,
     * "shifts": [
     *   {
     *      "id": 3,
     *     "start_time": "09:00:00",
     *    "end_time": "15:00:00",
     *   "start_break_hour": "12:30:00",
     *   "end_break_hour": "13:00:00"
     *},
     *{
     *   "id": 4,
     *   "start_time": "16:00:00",
     *  "end_time": "22:00:00",
     *  "start_break_hour": "20:30:00",
     *  "end_break_hour": "21:00:00"
     *}
     * ],
     * "deposits": []
     * }
     *}
     */
    public function update_employee(UpdateEmployeeRequest $request)
    {
        $createdData =  $this->adminService->update_employee($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = EmployeeResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Determine Vacation Hours For Employee
     *
     * This endpoint is used to DDetermine Vacation Hours For Employee in the Company and Admin Or Hr Can Access To This Api.
     *
     *@bodyParam user_id int required Must Be Exists In Users Table
     * @bodyParam hours_daily int  The number of hours Vacation per month.
     * @bodyParam hours_annual int  The number of hours Vacation per year.
     * @response 200 scenario="Determine Working Hours For Employee"{
     *"id": 2,
     *"user_id": 8,
     *"hours_daily": 10,
     *"hours_annual": 25
     * }
     */
    public function determine_working_hours(DetermineWorkingHoursRequest $request)
    {
        $createdData =  $this->adminService->determine_working_hours($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = EmployeeAvailableTimeResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    /**
     * Update Vacation Hours For Employee
     *
     * This endpoint is used to Update Vacation Hours For Employee in the Company and Admin Or Hr Can Access To This Api.
     *
     *@bodyParam user_id int required Must Be Exists In Users Table
     * @bodyParam hours_daily int  The number of hours Vacation per month.
     * @bodyParam hours_annual int  The number of hours Vacation per year.
     * @response 200 scenario="Determine Working Hours For Employee"{
     *"id": 2,
     *"user_id": 8,
     *"hours_daily": 30,
     *"hours_annual": 80
     * }
     */
    public function update_working_hours(DetermineWorkingHoursRequest $request)
    {
        $createdData =  $this->adminService->update_working_hours($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = EmployeeAvailableTimeResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Update Employee Salary For Employee
     *
     * This endpoint is used to Update Employee Salary in the Company and Admin Or Hr Can Access To This Api.
     *
     *@bodyParam user_id int required Must Be Exists In Users Table
     * @bodyParam new_salary number required Refers To New Salary Custom Example:75000.
     * @response 200 scenario="Update Employee Salary"{
     *"data": {
     *"id": 6,
     *"name": "Firas Jabi",
     *"email": "firassaljabi1232@gmail.com",
     *"work_email": "firassaljabi1237@goma.com",
     *"status": 1,
     *"type": 2,
     *"gender": 1,
     *"mobile": "0969040342",
     *"phone": "0935463111",
     *"departement": null,
     *"address": "Damascus",
     *"position": null,
     *"skills": null,
     *"serial_number": "00011",
     *"birthday_date": "1998-11-26",
     *"marital_status": null,
     *"guarantor": null,
     *"branch": "syria branch",
     *"start_job_contract": null,
     *"end_job_contract": null,
     *"end_visa": null,
     *"end_passport": null,
     *"end_employee_sponsorship": null,
     *"end_municipal_card": null,
     *"end_health_insurance": null,
     *"end_employee_residence": null,
     *"image": null,
     *"id_photo": null,
     *"biography": null,
     *"employee_sponsorship": null,
     *"visa": null,
     *"passport": null,
     *"municipal_card": null,
     *"health_insurance": null,
     *"employee_residence": null,
     *"permission_to_entry": 0,
     *"entry_time": null,
     *"permission_to_leave": 0,
     *"leave_time": null,
     *"basic_salary": 750000
     *"percentage": "0",
     *}
     * }
     */
    public function update_salary(UpdateSalaryRequest $request)
    {
        $createdData =  $this->adminService->update_salary($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = EmployeeResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Renewal Employee Contract
     *
     * This endpoint is used to  Renewal Employee Contract in the Company and Admin Or Hr Can Access To This Api.
     *
     *@bodyParam user_id int required Must Be Exists In Users Table
     *@bodyParam new_date date Must Be Required if number_of_month equal null Custom Example: 2025-05-01.
     *
     * @bodyParam number_of_month int Must Be Required if new_date equal null, Must be one of the following values:
     * - `3`: Three Month.
     * - `6`: Six Month.
     * - `12`: One Year. Custom Example: 12
     *
     * @response 200 scenario=" Renewal Employee Contract"{
     *"data": {
     *"id": 1,
     *"Start Employee Contract Date": "2023-08-01",
     *"End Employee Contract Date": "2025-05-01",
     *"Contract Termination Date": null,
     *"Contract Termination Period": null,
     *"Contract Termination Reason": null,
     *"user": {
     *"id": 3,
     *"name": "mouaz alkhateeb"
     *}
     *}
     */
    public function update_employment_contract(UpdateEmployeeContractRequest $request)
    {
        $createdData =  $this->adminService->update_employment_contract($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = ContractResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Termination Employee Contract
     *
     * This endpoint is used to Termination Employee Contract in the Company and Admin Or Hr Can Access To This Api.
     *
     * @bodyParam user_id int required Must Be Exists In Users Table
     * @bodyParam contract_termination_period int required. Must be one of the following values:
     * - `0`: Open Term.
     * - `3`: Three Month.
     * - `6`: Six Month.
     * - `12`: One Year. Custom Example: 3
     *
     * @bodyParam contract_termination_reason string required  Custom Example: Without Reason
     *
     * @response 200 scenario="Termination Employee Contract"{
     * "data": {
     * "id": 4,
     * "Start Employee Contract Date": "2023-09-05",
     * "End Employee Contract Date": "2024-01-09",
     * "Contract Termination Date": "2023-08-27",
     * "Contract Termination Period": "3 months, 0 days",
     * "Contract Termination Reason": "Without Reason",
     * "user": {
     * "id": 8,
     * "name": "mouaz alkhateeb"
     *}
     * }
     *}
     */
    public function termination_employees_contract(TerminateContractRequest $request)
    {
        $createdData =  $this->adminService->termination_employees_contract($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = ContractResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    /**
     * Employee check in System Daily
     *
     * This endpoint is used to check in System in the Company and Employee Can Access To This Api.
     *
     * @bodyParam check_in int required Must Be Value 1 Custom Example: 1

     * @response 200 scenario="Employee check in System Daily"{
     *"data": {
     *"id": 2,
     *"Date": "2023-08-27",
     *"login_time": "14:03:19",
     *"logout_time": null,
     *"user": {
     *"id": 9,
     *"name": "ahmad alkhateeb"
     *}
     *}
     * }
     */
    public function check_in_attendance(CheckInAttendanceRequest $request)
    {
        $createdData =  $this->adminService->check_in_attendance($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = AttendanceResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    /**
     * Employee Out in System Daily
     *
     * This endpoint is used to check Out From System in the Company and Employee Can Access To This Api.
     *
     * @bodyParam check_out    int required Must Be Value 1 Custom Example: 1

     * @response 200 scenario="Employee check Out System Daily"{
     *"data": {
     *"id": 2,
     *"Date": "2023-08-27",
     *"login_time": "14:03:19",
     *"logout_time": null,
     *"user": {
     *"id": 9,
     *"name": "ahmad alkhateeb"
     *}
     *}
     * }
     */

    public function check_out_attendance(CheckOutAttendanceRequest $request)
    {
        $createdData =  $this->adminService->check_out_attendance($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = AttendanceResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    /**
     * Add Reward Adversaries allowance For Employee
     *
     * This endpoint is used to Add Reward Adversaries allowance For Employee in the Company and Admin Or Hr Can Access To This Api.
     *
     *@bodyParam user_id int required Must Be Exists In Users Table

     * @bodyParam rewards_type int optional The type of rewards to user. Must be one of the following values:
     * - `1`: Number.
     * - `2`: Rate. Custom Example: 1
     *
     * @bodyParam rewards double This Field Required if rewards type not equal null Custom Example: 25000, Custom Example:5.
     *
     * @bodyParam adversaries_type  int optional The type of adversaries to user. Must be one of the following values:
     * - `1`: Number.
     * - `2`: Rate. Custom Example: 1
     *
     * @bodyParam adversaries double This Field Required if adversaries type not equal null Custom Example: 78000, Custom Example:3.
     *
     * @bodyParam housing_allowance double Custom Example: 100000.
     *
     * @bodyParam transportation_allowance  double Custom Example: 80000.
     *
     * @response 200 scenario="Add Reward Adversaries allowance For Employee"{
     *"Net_Salary": 220000,
     *"Rewards": 10,
     *"Adversaries": 0,
     *"Housing allowance": 0,
     *"Transportation Allowance": 0,
     *"date": "2023-08-27",
     *"user": {
     *"id": 8,
     *"name": "mouaz alkhateeb"
     *}
     * }
     */

    public function reward_adversaries_allowance_salary(RewerdsAdversriesSalaryRequest $request)
    {
        $createdData =  $this->adminService->reward_adversaries_allowance_salary($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = SalaryResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Delete Employee From the Company
     *
     * This endpoint is used to Delete Employee From the Company and Admin Or Hr Can Access To This Api.
     *
     *@urlParam id int required Must Be Exists In Users Table
     * @response 200 scenario="Delete a Employee"{
     *
     * }
     */
    public function destroyEmployee($id)
    {
        $deletionResult = $this->adminService->deleteEmployee($id);

        if (is_string($deletionResult)) {
            return ApiResponseHelper::sendErrorResponse(
                new ErrorResult($deletionResult)
            );
        }

        return ApiResponseHelper::sendSuccessResponse(
            new SuccessResult("Done", $deletionResult)
        );
    }

    /**
     * Get Data Dashboard Statics in the Company
     *
     * This endpoint is used to Show Data In Dashboard in the Company and Admin Or Hr Can Access To This Api.
     *
     * @response 200 scenario="Dashboard Data"{
     *"all_employees_count": "1",
     *"attendance_rate": "0",
     *"on_duty_employees_count": "1",
     *"on_vacation_employees_count": "0",
     *"on_duty_employees_percentage": "100",
     *"on_vacation_employees_percentage": "0",
     *"male_employees_percentage": "0",
     *"female_employees_percentage": "0",
     *"nationalities_rate": [
     *{
     *"nationality_id": 2,
     *"nationality_name": "Syrian",
     *"percent": 100
     *}
     *],
     *"contract_expiration_percentage": "0",
     *"contract_expiration": [],
     *"expired_passports_percentage": "100",
     *"expired_passports": [
     *{
     *"id": 8,
     *"name": "mouaz alkhateeb",
     *"end_passport": "2023-09-11"
     *}
     *]
     * }
     */
    public function getDashboardData()
    {
        $data = $this->adminService->getDashboardData();
        $returnData = DashboardDataResource::make($data);
        return ApiResponseHelper::sendResponse(
            new Result($returnData, "DONE")
        );
    }

    /**
     * Get Employees List in the Company
     *
     * This endpoint is used to Show Employees List in the Company and Admin Or Hr Can Access To This Api.
     *@queryParam name string Filter Employees By Name Custom Example: mouaz
     * @response 200 scenario="Employees List"{
     *"data": [
     *{
     *"id": 8,
     *"name": "mouaz alkhateeb",
     *"email": "mouaz@gmail.com",
     *"work_email": "mouazalkhateeb@gmail.com",
     *"status": 1,
     *"type": 4,
     *"gender": 1,
     *"mobile": "0969040322",
     *"phone": "0969040322",
     *"departement": "it",
     *"address": "Damascus",
     *"position": null,
     *"skills": "no skills",
     *"serial_number": "000007",
     *"birthday_date": "2022-11-26",
     *"marital_status": null,
     *"guarantor": "admin",
     *"branch": "syria branch",
     *"start_job_contract": "2023-08-01",
     *"end_job_contract": "2023-10-01",
     *"end_visa": "2023-09-11",
     *"end_passport": "2023-09-11",
     *"end_employee_sponsorship": null,
     *"end_municipal_card": "2023-09-10",
     *"end_health_insurance": "2023-09-14",
     *"end_employee_residence": "2023-09-20",
     *"image": "http://127.0.0.1:8000/employees/2023-08-27-Employee-8.jpg",
     *"id_photo": null,
     *"biography": "http://127.0.0.1:8000/employees/2023-08-27-Employee-test.pdf",
     *"employee_sponsorship": "http://127.0.0.1:8000/employees/2023-08-27-Employee-55.jpg",
     *"visa": "http://127.0.0.1:8000/employees/2023-08-27-Employee-7.jpg",
     *"passport": "http://127.0.0.1:8000/employees/2023-08-27-Employee-6.jpeg",
     *"municipal_card": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpeg",
     *"health_insurance": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpg",
     *"employee_residence": "http://127.0.0.1:8000/employees/2023-08-27-Employee-77.jpg",
     *"permission_to_entry": 1,
     *"entry_time": 30,
     *"permission_to_leave": 1,
     *"leave_time": 60,
     *"percentage": "0",
     *"basic_salary": 200000
     *},
     *{
     *"id": 9,
     *"name": "ahmad alkhateeb",
     *"email": "ahmad@gmail.com",
     *"work_email": "ahmadlkhateeb@gmail.com",
     *"status": 1,
     *"type": 4,
     *"gender": 1,
     *"mobile": "0969040355",
     *"phone": "0969040344",
     *"departement": "it",
     *"address": "Damascus",
     *"position": "laravel developer",
     *"skills": "no skills",
     *"serial_number": "000011",
     *"birthday_date": "2022-11-26",
     *"marital_status": null,
     *"guarantor": "User",
     *"branch": "syria branch",
     *"start_job_contract": "2023-08-01",
     *"end_job_contract": "2023-10-01",
     *"end_visa": "2023-09-11",
     *"end_passport": "2023-09-11",
     *"end_employee_sponsorship": null,
     *"end_municipal_card": "2023-09-10",
     *"end_health_insurance": "2023-09-14",
     *"end_employee_residence": "2023-09-20",
     *"image": "http://127.0.0.1:8000/employees/2023-08-27-Employee-8.jpg",
     *"id_photo": null,
     *"biography": "http://127.0.0.1:8000/employees/2023-08-27-Employee-test.pdf",
     *"employee_sponsorship": "http://127.0.0.1:8000/employees/2023-08-27-Employee-55.jpg",
     *"visa": "http://127.0.0.1:8000/employees/2023-08-27-Employee-7.jpg",
     *"passport": "http://127.0.0.1:8000/employees/2023-08-27-Employee-6.jpeg",
     *"municipal_card": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpeg",
     *"health_insurance": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpg",
     *"employee_residence": "http://127.0.0.1:8000/employees/2023-08-27-Employee-77.jpg",
     *"permission_to_entry": 1,
     *"entry_time": 30,
     *"permission_to_leave": 1,
     *"leave_time": 60,
     *"percentage": "0",
     *"basic_salary": 200000
     * }
     *]
     * }
     */
    public function getEmployeesList(GetEmployeesListRequest $request)
    {
        $data = $this->adminService->getEmployees($request->generateFilter());
        $returnData = EmployeeResource::collection($data);
        return ApiResponseHelper::sendResponse(
            new Result($returnData, "DONE")
        );
    }

    /**
     * Show Salaries in the Company
     *
     * This endpoint is used to Show Salaries in the Company and Admin Or Hr Can Access To This Api.
     *
     * @response 200 scenario="Show Salaries"{
     *"data": [
     *{
     *"Net_Salary": 220000,
     *"Rewards": 10,
     *"Adversaries": 0,
     *"Housing allowance": 0,
     *"Transportation Allowance": 0,
     *"date": "2023-08-27",
     *"user": {
     *"id": 8,
     *"name": "mouaz alkhateeb"
     *}
     *},
     *{
     *"Net_Salary": 600000,
     *"Rewards": 10,
     *"Adversaries": 0,
     *"Housing allowance": 0,
     *"Transportation Allowance": 0,
     *"date": "2023-08-27",
     *"user": {
     *"id": 9,
     *"name": "ahmad alkhateeb"
     *}
     *}
     *]
     * }
     */
    public function employees_salaries(GetEmployeesSalariesListRequest $request)
    {
        $data = $this->adminService->employees_salaries($request->generateFilter());

        if ($data['success']) {
            $newData = $data['data'];
            $returnData = SalaryResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }
    /**
     * Show Employees Attendance in the Company
     *
     * This endpoint is used to Show Employees Attendance in the Company and Admin Or Hr Can Access To This Api.
     *
     * @response 200 scenario="Employees Attendance"{
     * "data": [
     * {
     *"id": 1,
     *"Date": "2023-08-27",
     * "login_time": "14:01:54",
     * "logout_time": "14:02:07",
     *"user": {
     *"id": 8,
     *"name": "mouaz alkhateeb"
     *}
     *},
     *{
     * "id": 2,
     * "Date": "2023-08-27",
     *"login_time": "14:03:19",
     * "logout_time": "14:03:27",
     * "user": {
     * "id": 9,
     * "name": "ahmad alkhateeb"
     *}
     *}
     *]
     * }
     */
    public function employees_attendances(GetEmployeesAttendancesListRequest $request)
    {
        $data = $this->adminService->employees_attendances($request->generateFilter());

        if ($data['success']) {
            $newData = $data['data'];
            $returnData = AttendanceResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }

    /**
     * Get Contract Expiration in the Company
     *
     * This endpoint is used to Get Contract Expiration in the Company and Admin Or Hr Can Access To This Api.
     *
     * @response 200 scenario="Get Contract Expiration"{
     *"data": [
     *{
     *"id": 4,
     *"Start Employee Contract Date": "2023-09-05",
     *"End Employee Contract Date": "2023-09-27",
     *"Contract Termination Date": null,
     *"Contract Termination Period": null,
     *"Contract Termination Reason": null,
     *"user": {
     *"id": 8,
     * "name": "mouaz alkhateeb"
     *}
     *}
     *]
     * }
     */
    public function get_contract_expiration(GetContractExpirationList $request)
    {
        $data = $this->adminService->get_contract_expiration($request->generateFilter());
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = ContractResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }
    /**
     * Get Employee
     *
     * This endpoint is used to Get Employee in Company and Admin Or Hr Can Access To This Api.
     *
     * @urlParam id int required Must Be Exists In Users Table
     *@response 201 scenario="Show Employee"{
     * "data": {
     * "id": 4,
     * "name": "mouaz alkhateeb",
     * "email": "mouaz@gmail.com",
     * "work_email": "mouazalkhateeb@gmail.com",
     * "status": "1",
     * "type": 4,
     * "gender": "1",
     * "mobile": "0969040322",
     * "phone": "0969040322",
     * "departement": "it",
     * "address": "Damascus",
     * "position": null,
     * "skills": "no skills",
     * "serial_number": "000007",
     * "birthday_date": "2022-11-26",
     * "marital_status": null,
     * "guarantor": "admin",
     *"branch": "syria branch",
     * "start_job_contract": "2023-08-01",
     * "end_job_contract": "2023-10-01",
     * "end_visa": "2023-09-11",
     * "end_passport": "2023-09-11",
     * "end_employee_sponsorship": null,
     * "end_municipal_card": "2023-09-10",
     * "end_health_insurance": "2023-09-14",
     * "end_employee_residence": "2023-09-20",
     * "image": "http://127.0.0.1:8000/employees/2023-08-27-Employee-8.jpg",
     *"id_photo": null,
     * "biography": "http://127.0.0.1:8000/employees/2023-08-27-Employee-test.pdf",
     * "employee_sponsorship": "http://127.0.0.1:8000/employees/2023-08-27-Employee-55.jpg",
     * "visa": "http://127.0.0.1:8000/employees/2023-08-27-Employee-7.jpg",
     *"passport": "http://127.0.0.1:8000/employees/2023-08-27-Employee-6.jpeg",
     * "municipal_card": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpeg",
     * "health_insurance": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpg",
     * "employee_residence": "http://127.0.0.1:8000/employees/2023-08-27-Employee-77.jpg",
     * "permission_to_entry": "1",
     * "entry_time": "30",
     *"permission_to_leave": "1",
     *"leave_time": "60",
     *"nationalitie": {
     *   "name": "Syrian"
     * },
     *"percentage": "0",
     * "basic_salary": 200000,
     * "shifts": [
     *   {
     *      "id": 3,
     *     "start_time": "09:00:00",
     *    "end_time": "15:00:00",
     *   "start_break_hour": "12:30:00",
     *   "end_break_hour": "13:00:00"
     *},
     *{
     *   "id": 4,
     *   "start_time": "16:00:00",
     *  "end_time": "22:00:00",
     *  "start_break_hour": "20:30:00",
     *  "end_break_hour": "21:00:00"
     *}
     * ],
     *"deposits": [
     *{
     *"id": 1,
     *"type": 2,
     *"status": 1,
     *"extra_status": null,
     *"laptop_type": "asus",
     *"Serial_laptop_number": "2010",
     *"laptop_color": "blue",
     *"laptop_image": "http://127.0.0.1:8000/employees_deposits/2023-08-27-EmployeeDeposit-844.png",
     *"reason_reject": null,
     *"reason_clearance_reject": null
     *}
     * ]
     * }
     *}
     */
    public function getEmployee($id)
    {
        $employeeData = $this->adminService->showEmployee($id);
        $returnData = EmployeeResource::make($employeeData);
        return ApiResponseHelper::sendResponse(
            new Result($returnData,  "DONE")
        );
    }
    /**
     * Show My Profile
     *
     * This endpoint is used to Show Personal Profile and Just Employee Can Access To This Api.
     *
     * @response 200 scenario="Show My Profile"{
     * "data": {
     * "id": 4,
     * "name": "mouaz alkhateeb",
     * "email": "mouaz@gmail.com",
     * "work_email": "mouazalkhateeb@gmail.com",
     * "status": "1",
     * "type": 4,
     * "gender": "1",
     * "mobile": "0969040322",
     * "phone": "0969040322",
     * "departement": "it",
     * "address": "Damascus",
     * "position": null,
     * "skills": "no skills",
     * "serial_number": "000007",
     * "birthday_date": "2022-11-26",
     * "marital_status": null,
     * "guarantor": "admin",
     *"branch": "syria branch",
     * "start_job_contract": "2023-08-01",
     * "end_job_contract": "2023-10-01",
     * "end_visa": "2023-09-11",
     * "end_passport": "2023-09-11",
     * "end_employee_sponsorship": null,
     * "end_municipal_card": "2023-09-10",
     * "end_health_insurance": "2023-09-14",
     * "end_employee_residence": "2023-09-20",
     * "image": "http://127.0.0.1:8000/employees/2023-08-27-Employee-8.jpg",
     *"id_photo": null,
     * "biography": "http://127.0.0.1:8000/employees/2023-08-27-Employee-test.pdf",
     * "employee_sponsorship": "http://127.0.0.1:8000/employees/2023-08-27-Employee-55.jpg",
     * "visa": "http://127.0.0.1:8000/employees/2023-08-27-Employee-7.jpg",
     *"passport": "http://127.0.0.1:8000/employees/2023-08-27-Employee-6.jpeg",
     * "municipal_card": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpeg",
     * "health_insurance": "http://127.0.0.1:8000/employees/2023-08-27-Employee-9.jpg",
     * "employee_residence": "http://127.0.0.1:8000/employees/2023-08-27-Employee-77.jpg",
     * "permission_to_entry": "1",
     * "entry_time": "30",
     *"permission_to_leave": "1",
     *"leave_time": "60",
     *"nationalitie": {
     *   "name": "Syrian"
     * },
     *"percentage": "0",
     * "basic_salary": 200000,
     * "shifts": [
     *   {
     *      "id": 3,
     *     "start_time": "09:00:00",
     *    "end_time": "15:00:00",
     *   "start_break_hour": "12:30:00",
     *   "end_break_hour": "13:00:00"
     *},
     *{
     *   "id": 4,
     *   "start_time": "16:00:00",
     *  "end_time": "22:00:00",
     *  "start_break_hour": "20:30:00",
     *  "end_break_hour": "21:00:00"
     *}
     * ],
     *"deposits": [
     *{
     *"id": 1,
     *"type": 2,
     *"status": 1,
     *"extra_status": null,
     *"laptop_type": "asus",
     *"Serial_laptop_number": "2010",
     *"laptop_color": "blue",
     *"laptop_image": "http://127.0.0.1:8000/employees_deposits/2023-08-27-EmployeeDeposit-844.png",
     *"reason_reject": null,
     *"reason_clearance_reject": null
     *}
     * ]
     * }
     *}
     */


    public function profile()
    {
        $employeeData = $this->adminService->profile();
        $returnData = EmployeeResource::make($employeeData);
        return ApiResponseHelper::sendResponse(
            new Result($returnData,  "DONE")
        );
    }
    /**
     * Show Nationalities List in the Company
     *
     * This endpoint is used to Show Nationalities List in the Company and Admin Or Hr Can Access To This Api.
     *
     * @response 200 scenario="Show Nationalities List"{
     *"data": [
     *{
     *"id": 1,
     *"name": "Saudi Arabian"
     *},
     *{
     *"id": 2,
     *"name": "Syrian"
     *},
     *{
     *"id": 3,
     * "name": "Afghan"
     * },
     * {
     *"id": 4,
     *"name": "Algerian"
     *},
     *{
     *"id": 5,
     *"name": "Argentinian"
     *},
     *{
     * "id": 6,
     *"name": "Bahraini"
     *},
     *{
     *"id": 7,
     *"name": "Bangladeshi"
     *},
     *{
     * "id": 8,
     *"name": "Belarusian"
     * },
     *{
     *"id": 9,
     * "name": "Belgian"
     *},
     *{
     *"id": 10,
     *"name": "Egyptian"
     *},
     *{
     *"id": 11,
     *"name": "Indian"
     *},
     *{
     *"id": 12,
     *"name": "Iraqi"
     *},
     *{
     * "id": 13,
     *"name": "Irish"
     *},
     * {
     * "id": 14,
     *"name": "Italian"
     *},
     *{
     *"id": 15,
     *"name": "Jordanian"
     *},
     *{
     *"id": 16,
     *"name": "Kuwaiti"
     *},
     * {
     *"id": 17,
     *"name": "Libyan"
     *},
     *{
     *"id": 18,
     *"name": "Moroccan"
     *},
     *{
     *"id": 19,
     *"name": "Pakistani"
     *},
     *{
     *"id": 20,
     *"name": "Palestinian"
     *},
     *{
     *"id": 21,
     *"name": "Filipino"
     *},
     *{
     *"id": 22,
     *"name": "Polish"
     *},
     *{
     *"id": 23,
     *"name": "Portuguese"
     *},
     *{
     *"id": 24,
     *"name": "Portuguese"
     *},
     *{
     * "id": 25,
     * "name": "Romanian"
     *},
     *{
     * "id": 26,
     *"name": "Qatari"
     *},
     *{
     *"id": 27,
     *"name": "Russian"
     *},
     * {
     * "id": 28,
     *"name": "Singaporean"
     *},
     *{
     *"id": 29,
     *"name": "Somali"
     *},
     *{
     *"id": 30,
     *"name": "Sudanese"
     *},
     * {
     * "id": 31,
     *"name": "Swedish"
     *},
     *{
     *"id": 32,
     *"name": "Swiss"
     *},
     * {
     * "id": 33,
     *"name": "Taiwanese"
     *},
     *{
     *"id": 34,
     *"name": "Tajikistani"
     *},
     *{
     *"id": 35,
     *"name": "Thai"
     * },
     *{
     *"id": 36,
     *"name": "Tunisian"
     *},
     *{
     * "id": 37,
     *"name": "Turkish"
     *},
     *{
     *"id": 38,
     * "name": "Ukrainian"
     *},
     *{
     *"id": 39,
     *"name": "Emirati"
     *},
     *{
     *"id": 40,
     *"name": "British"
     *},
     *{
     *"id": 41,
     *"name": "American"
     *},
     *{
     *"id": 42,
     * "name": "Uruguayan"
     *},
     *{
     *"id": 43,
     *"name": "Uzbek"
     *},
     *{
     *"id": 44,
     *"name": "Venezuelan"
     *},
     *{
     *"id": 45,
     *"name": "Yemeni"
     *},
     *{
     * "id": 46,
     * "name": "South African"
     *},
     *{
     *"id": 47,
     * "name": "Serbian"
     *},
     *{
     * "id": 48,
     * "name": "Panamanian"
     *},
     *{
     *"id": 49,
     *"name": "Omani"
     *},
     * {
     * "id": 50,
     *"name": "Norwegian"
     * },
     *{
     *"id": 51,
     *"name": "Nigerian"
     *},
     *{
     *"id": 52,
     *"name": "Dutch"
     *},
     *{
     * "id": 53,
     * "name": "Mexican"
     *},
     *{
     * "id": 54,
     *"name": "Mauritanian"
     *},
     *{
     *"id": 55,
     *"name": "Malaysian"
     *},
     *{
     *"id": 56,
     *"name": "Japanese"
     *}
     *]
     * }
     */
    public function list_of_nationalities(GetNationalitiesRequest $request)
    {
        $data = $this->adminService->list_of_nationalities($request->generateFilter());
        $returnData = NationalitiesRrsource::collection($data);
        return ApiResponseHelper::sendResponse(
            new Result($returnData, "DONE")
        );
    }
}
