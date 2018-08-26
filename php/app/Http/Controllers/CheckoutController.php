<?php

namespace App\Http\Controllers;

use App\{Order, Event};
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{

    public function store(Request $request, $eventId)
    {
        $crypto = $request->input('crypto', 0);
        $free   = $request->input('free', 0);

        $event = Event::where('id', '=', $eventId)
            ->whereDate('start_at', '>', new Carbon('+1 days'))
            ->firstOrFail();
        $total = Order::where('event_id', '=', $event->id)
            ->whereIn('status', [Order::PAID, Order::CONFIRM])
            ->sum('total');

        $total = ($crypto + $free);
        if ($total > ($event->max - $total)) {
            throw new GeneralException(400, '活動剩餘人數不足');
        }

        if (!$total) {
            throw new GeneralException(400, '請輸入大於0的票數');
        }

        $amount    = $event->price * $crypto;
        $reference = substr(str_shuffle(str_repeat($x = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(8 / strlen($x)))), 1, 8);

        // Create new order
        $order            = new Order();
        $order->reference = $reference;
        $order->amount    = $amount;
        $order->total     = $total;
        $order->event_id  = $eventId;
        $order->save();

        return view('checkout.store', compact('event', 'order'));
    }
}