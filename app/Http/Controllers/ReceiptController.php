<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receipt;

class ReceiptController extends Controller
{
    // public function print(Receipt $receipt)
    // {
    //     $receipt->load('deposit.tenancy.tenant', 'deposit.tenancy.unit.property');

    //     return view('receipts.print', compact('receipt'));
    // } 
    
    public function print(Receipt $receipt)
{
    $receipt->loadMissing(
        'deposit.tenancy.tenant',
        'deposit.tenancy.unit.property'
    );

    if (!$receipt->deposit) {
        abort(404, 'Deposit not found for receipt');
    }

    return view('receipts.print', compact('receipt'));
}
}
