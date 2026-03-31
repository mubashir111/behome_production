<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\ReturnAndRefund;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReturnAndRefundExport;
use App\Http\Requests\PaginateRequest;
use App\Services\ReturnAndRefundService;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Resources\ReturnAndRefundResource;
use App\Http\Resources\ReturnAndRefundDetailsResource;

class ReturnAndRefundController extends AdminController
{
    private ReturnAndRefundService $returnAndRefundService;

    public function __construct(ReturnAndRefundService $returnAndRefundService)
    {
        parent::__construct();
        $this->returnAndRefundService = $returnAndRefundService;
        $this->middleware(['permission:return-and-refunds'])->only('index', 'show', 'export', 'changeStatus');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ReturnAndRefundResource::collection($this->returnAndRefundService->list($request, false));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(ReturnAndRefund $returnAndRefund): \Illuminate\Foundation\Application|\Illuminate\Http\Response|ReturnAndRefundDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ReturnAndRefundDetailsResource($this->returnAndRefundService->show($returnAndRefund, false));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(ReturnAndRefund $returnAndRefund, OrderStatusRequest $request): \Illuminate\Http\Response|ReturnAndRefundDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ReturnAndRefundDetailsResource($this->returnAndRefundService->changeStatus($returnAndRefund, $request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new ReturnAndRefundExport($this->returnAndRefundService, $request), 'Return-Order.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
