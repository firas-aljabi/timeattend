<?php

namespace App\Http\Controllers\Api;

use App\ApiHelper\ApiResponseHelper;
use App\ApiHelper\Result;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\CreateEmployeeRequest;
use App\Http\Requests\Employees\GetMonthlyShiftListRequest;
use App\Http\Requests\Requests\CreateJustifyRequest;
use App\Http\Requests\Requests\CreateResignationRequest;
use App\Http\Requests\Requests\CreateRetirementRequest;
use App\Http\Requests\Requests\CreateVacationRequest;
use App\Http\Requests\Requests\GetRequestListRequest;
use App\Http\Requests\Requests\RejectVacationRequest;
use App\Http\Resources\Requests\RequestResource;
use App\Services\Requests\RequestService;

/**
 * @group Requests
 *
 * APIs for managing Requests
 */
class RequestController extends Controller
{
    public function __construct(private RequestService $requestService)
    {
    }
    public function add_vacation_request(CreateVacationRequest $request)
    {
        $createdData =  $this->requestService->add_vacation_request($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = RequestResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    public function add_justify_request(CreateJustifyRequest $request)
    {
        $createdData =  $this->requestService->add_justify_request($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = RequestResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    public function add_retirement_request(CreateRetirementRequest $request)
    {
        $createdData =  $this->requestService->add_retirement_request($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = RequestResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    public function add_resignation_request(CreateResignationRequest $request)
    {
        $createdData =  $this->requestService->add_resignation_request($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = RequestResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    public function show($id)
    {
        $request = $this->requestService->show($id);
        if ($request['success']) {
            $newData = $request['data'];
            $returnData = RequestResource::make($newData);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $request['message']];
        }
    }

    public function approve_request($id)
    {
        $vacationRequest = $this->requestService->approve_request($id);
        if ($vacationRequest['success']) {
            $newData = $vacationRequest['data'];
            $returnData = RequestResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $vacationRequest['message']];
        }
    }
    public function reject_request(RejectVacationRequest $request)
    {
        $createdData =  $this->requestService->reject_request($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = RequestResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    public function my_requests(GetRequestListRequest $request)
    {
        $data = $this->requestService->my_requests($request->generateFilter());
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = RequestResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }
    public function vacation_requests()
    {
        $data = $this->requestService->vacation_requests();
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = RequestResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }
    public function justify_requests()
    {
        $data = $this->requestService->justify_requests();
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = RequestResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }
    public function retirement_requests()
    {
        $data = $this->requestService->retirement_requests();
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = RequestResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }
    public function resignation_requests()
    {
        $data = $this->requestService->resignation_requests();
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = RequestResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }
    public function getMonthlyData(GetMonthlyShiftListRequest $request)
    {
        $data = $this->requestService->getMonthlyData($request->generateFilter());
        return $data;
    }
}