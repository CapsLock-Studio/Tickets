<?php

namespace App\Http\Controllers;

use App\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckinController extends Controller
{

    public function index(Request $request, $reference)
    {
        $order = Order::where('reference', $reference)
            ->whereDate('start_at', '<=', Carbon::now())
            ->first();

        if ($order && $order->status === Order::PAID) {
            $order->status = Order::CONFIRM;
            $order->save();
        }

        return view('checkin.index', compact('order'));
    }
}
