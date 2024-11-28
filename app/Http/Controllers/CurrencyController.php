<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


class CurrencyController extends Controller
{

    /**
     * @OA\Post(
     *     path="/currency-exchange",
     *     operationId="currencyExchange",
     *     tags={"Currency"},
     *     summary="Fetch and store currency exchange rate",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="usd", description="Currency code to fetch the exchange rate for")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Currency exchange rate fetched successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid currency code",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     )
     * )
     */

    public function currencyExchange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            $code = $request->code;
            $isValidCurrency = Currency::whereCode($code)->first();
            if ($isValidCurrency) {
                $res = Http::get("https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/{$code}.json");
                if ($res->status() == 200) {
                    $data = $res->json();
                    $exchange_rate = $data[$code];
                    $date = $data['date'];
                    $ExchangeRate = ExchangeRate::updateOrCreate([
                        'code' => $code,
                        'date' => $date
                    ], [
                        'currency_id' => $isValidCurrency->id,
                        'exchange_rate' => $exchange_rate
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'result' => 'Currency exchange rate fetched successfully',
                        'data' => json_decode(json_encode($ExchangeRate->exchange_rate))
                    ]);
                } else {
                    $ExchangeRate = ExchangeRate::whereCode($code)->whereDate(date('Y-m-d'))->first();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Currency exchange rate fetched successfully',
                        'data' => json_decode(json_encode($ExchangeRate->exchange_rate))
                    ]);
                }

            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid currency code',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }
}
