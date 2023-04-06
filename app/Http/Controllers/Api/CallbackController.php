<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\MidtransHistory;

class CallbackController extends Controller
{
    public function callback(Request $request) {
        $payload = $request->all();

        Log::info('incoming-midtrans', [
            'payload' => $payload
        ]);

        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];

        $reqSignature = $payload['signature_key'];

        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.sandbox_server_key'));

        if($signature != $reqSignature) {
            return response()->json([
                'message' => 'invalid signature'
            ], 401);
        }

        $transactionStatus = $payload['transaction_status'];

        $midtransHistory = new MidtransHistory;
        $midtransHistory->order_id = $orderId;
        $midtransHistory->status = $transactionStatus;
        $midtransHistory->payload = json_encode($payload);
        $midtransHistory->save();

        $order = Transaction::find($orderId);
        if(!$order) {
            return response()->json([
                'message' => 'invalid order id'
            ], 400);
        }

        if($transactionStatus == 'settlement') {
            $order->status = 'PAID';
            $order->save();
        } else if($transactionStatus == 'expire') {
            $order->status = 'EXPIRED';
            $order->save();
        }

        return response()->json([
            'message' => 'success',
        ]);
    }
}
