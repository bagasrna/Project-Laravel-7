<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


class TicketController extends Controller
{
    public function buy(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'total' => 'required|numeric',
            'ticket_id' => 'required|exists:tickets,id',
            'bank' => 'required|in:bca,bni',
        ]);
        if($validator->fails()) {
            return response()->json([
                'message' => 'invalid',
                'data' => $validator->errors()
            ]);
        }

        $ticket = Ticket::find($request->ticket_id);

        try {
            DB::beginTransaction();

            $serverKey = config('midtrans.production_server_key');

            $orderId = Str::uuid()->toString();
            $grossAmount = $ticket->price * $request->total;

            $response = Http::withBasicAuth($serverKey, '')
            ->post(config('midtrans.production_url'), [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                // 'bank_transfer' => [
                //     'bank' => $request->bank,
                // ],
                'customer_details' => [
                    'email' => $request->email,
                    'first_name' => 'CUSTOMER ',
                    'last_name' => $request->name,
                    'phone' => '082234018230',
                ]
            ]);

            if($response->failed()) {
                return response()->json([
                    'message' => 'failed charge'
                ], 500);
            }

            $result = $response->json();
            if($result['status_code'] != '201') {
                return response()->json([
                    'message' => $result['status_message']
                ], 500);
            }

            $transaction = new Transaction;
            $transaction->id = $orderId;
            $transaction->booking_code = Str::random(6);
            $transaction->name = $request->name;
            $transaction->email = $request->email;
            $transaction->ticket_id = $request->ticket_id;
            $transaction->total_ticket = $request->total;
            $transaction->total_amount = $grossAmount;
            $transaction->status = 'BOOKED';
            $transaction->save();

            $ticket->stock = $ticket->stock - $request->total;
            $ticket->save();

            DB::commit();

            return response()->json([
                'data' => $result,
                // 'va' => $result['va_numbers'][0]['va_number'],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
