<?php

namespace App\Http\Controllers\Api;

use App\ApiHelper\ApiResponseHelper;
use App\ApiHelper\Result;
use App\Http\Controllers\Controller;
use App\Http\Requests\Deposit\CreateDepositRequest;
use App\Http\Requests\Deposit\GetDepositsList;
use App\Http\Requests\Deposit\RejectClearanceRequest;
use App\Http\Requests\Deposit\RejectDepositRequest;
use App\Http\Resources\Deposit\DepositResource;
use App\Services\Deposit\DepositService;



/**
 * @group Deposits
 *
 * APIs for managing Deposits
 */
class DepositController extends Controller
{

    public function __construct(private DepositService $depositService)
    {
    }
    public function store(CreateDepositRequest $request)
    {
        $createdData =  $this->depositService->create_deposit($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = DepositResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    public function approve_deposit($id)
    {
        $createdData = $this->depositService->approve_deposit($id);
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = DepositResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    public function reject_deposit(RejectDepositRequest $request)
    {
        $createdData = $this->depositService->reject_deposit($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = DepositResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }


    public function clearance_request($id)
    {
        $createdData = $this->depositService->clearance_request($id);
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = DepositResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    public function approve_clearance_request($id)
    {
        $createdData = $this->depositService->approve_clearance_request($id);
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = DepositResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    public function reject_clearance_request(RejectClearanceRequest $request)
    {
        $createdData = $this->depositService->reject_clearance_request($request->validated());
        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = DepositResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    public function my_deposits(GetDepositsList $request)
    {
        $data = $this->depositService->my_deposits($request->generateFilter());
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = DepositResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData,  "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }

    public function list_of_deposits(GetDepositsList $request)
    {
        $data = $this->depositService->list_of_deposits($request->generateFilter());
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = DepositResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData,  "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }

    public function list_of_clearance_deposits()
    {
        $data = $this->depositService->list_of_clearance_deposits();
        if ($data['success']) {
            $newData = $data['data'];
            $returnData = DepositResource::collection($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData,  "DONE")
            );
        } else {
            return ['message' => $data['message']];
        }
    }
}
