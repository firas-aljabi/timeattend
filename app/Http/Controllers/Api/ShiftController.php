<?php

namespace App\Http\Controllers\Api;

use App\ApiHelper\ApiResponseHelper;
use App\ApiHelper\Result;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\UpdateEmployeeShift;
use App\Http\Resources\Shifts\ShiftRersource;
use App\Services\Shift\ShiftSevice;
use Illuminate\Http\Request;

/**
 * @group Shifts
 *
 * APIs for managing Shifts
 */
class ShiftController extends Controller
{
    public function __construct(private ShiftSevice $shiftSevice)
    {
    }
    public function update_employee_shift(UpdateEmployeeShift $request)
    {
        $createdData =  $this->shiftSevice->update_employee_shift($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = ShiftRersource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
}