<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\P2PTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Wallet",
 *     description="Wallet operations such as view balance, fund wallet, transfer, and transactions"
 * )
 */
class WalletController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/wallet",
     *     summary="Get wallet balance",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Wallet balance",
     *         @OA\JsonContent(
     *             @OA\Property(property="balance", type="number", example=10000),
     *             @OA\Property(property="currency", type="string", example="NGN")
     *         )
     *     )
     * )
     */
    public function wallet()
    {
        $user = Auth::user();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'NGN']
        );

        return response()->json([
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/wallet/transactions",
     *     summary="Get wallet transaction history",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of wallet transactions"
     *     )
     * )
     */
    public function transactions()
    {
        $wallet = Auth::user()->wallet;

        if (!$wallet) {
            return response()->json(['transactions' => []]);
        }

        return response()->json([
            'transactions' => $wallet->transactions()->latest()->get()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/wallet/transfer",
     *     summary="Transfer money to another user",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"receiver_id", "amount"},
     *             @OA\Property(property="receiver_id", type="integer", example=2),
     *             @OA\Property(property="amount", type="number", example=2500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transfer successful")
     *         )
     *     )
     * )
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:100',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if ($wallet->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        // Debit sender
        $wallet->decrement('balance', $request->amount);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => $request->amount,
            'ref' => 'p2p_' . uniqid(),
            'status' => 'success'
        ]);

        // Credit receiver
        $receiverWallet = Wallet::firstOrCreate(
            ['user_id' => $request->receiver_id],
            ['balance' => 0, 'currency' => 'NGN']
        );

        $receiverWallet->increment('balance', $request->amount);

        WalletTransaction::create([
            'wallet_id' => $receiverWallet->id,
            'type' => 'credit',
            'amount' => $request->amount,
            'ref' => 'p2p_' . uniqid(),
            'status' => 'success'
        ]);

        P2PTransfer::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'amount' => $request->amount,
            'status' => 'completed',
        ]);

        return response()->json(['message' => 'Transfer successful']);
    }

    /**
     * @OA\Post(
     *     path="/api/wallet/fund",
     *     summary="Fund wallet using Paystack",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", example=5000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Funding initialized",
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_link", type="string", example="https://paystack.com/pay/xxxxx"),
     *             @OA\Property(property="reference", type="string", example="1234-xxxx-4567")
     *         )
     *     )
     * )
     */
    public function fundWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $user = Auth::user();
        $amountInKobo = $request->amount * 100;
        $ref = Str::uuid();

        $fields = [
            'email' => $user->email,
            'amount' => $amountInKobo,
            'reference' => $ref,
            'callback_url' => url('/'),
            'metadata' => [
                'user_id' => $user->id,
            ],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => env('PAYSTACK_PAYMENT_URL') . '/transaction/initialize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
                "Content-Type: application/json",
            ],
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($fields),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        if (isset($result->status) && $result->status) {
            WalletTransaction::create([
                'wallet_id' => $user->wallet->id,
                'type' => 'credit',
                'amount' => $request->amount,
                'ref' => $ref,
                'status' => 'pending',
            ]);

            return response()->json([
                'payment_link' => $result->data->authorization_url,
                'reference' => $ref
            ]);
        }

        return response()->json(['error' => 'Payment initiation failed'], 500);
    }

    /**
     * @OA\Post(
     *     path="/api/wallet/webhook",
     *     summary="Paystack webhook for confirming wallet fund",
     *     tags={"Wallet"},
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed"
     *     )
     * )
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        if ($payload['event'] !== 'charge.success') {
            return response()->json(['ignored' => true]);
        }

        $data = $payload['data'];
        $ref = $data['reference'];
        $amount = $data['amount'] / 100;

        $transaction = WalletTransaction::where('ref', $ref)->first();

        if (!$transaction || $transaction->status === 'success') {
            return response()->json(['message' => 'Already processed']);
        }

        $wallet = $transaction->wallet;
        $wallet->increment('balance', $amount);

        $transaction->status = 'success';
        $transaction->save();

        return response()->json(['message' => 'Wallet funded']);
    }
}
