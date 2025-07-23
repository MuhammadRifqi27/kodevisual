<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DetailExpensesController extends Controller
{
    public function index()
    {
        return view('pages.financial_summary.detail-expenses-rifqi');
    }

    public function createDetailExpensesRifqi()
    {
        return view('pages.financial_summary.create-detail-expenses-rifqi');
    }

    public function detailExpensesdatatable()
    {
        $payment_method = PaymentMethod::query();

        return datatables()->of($payment_method)
            ->addColumn('action', function ($data) {
                $buttons = '';

                // if (auth()->user()->can('area-promosi-edit')) {
                $buttons .= '<button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 edit-btn" data-id="' . $data->id . '">
                    <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>';
                // }

                // if (auth()->user()->can('area-promosi-delete')) {
                $buttons .= '<button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-btn" data-id="' . $data->id . '">
                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                </button>';
                // }

                return $buttons;
            })
            ->addColumn('DT_RowIndex', function ($data) {
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
