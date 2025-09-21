<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZaloPayService
{
    private $appId;
    private $key1;
    private $key2;
    private $callbackUrl;
    private $redirectUrl;
    private $endpoint;

    public function __construct()
    {
        $this->appId = config('services.zalopay.app_id');
        $this->key1 = config('services.zalopay.key1');
        $this->key2 = config('services.zalopay.key2');
        $this->callbackUrl = config('services.zalopay.callback_url');
        $this->redirectUrl = config('services.zalopay.redirect_url');
        $this->endpoint = config('services.zalopay.endpoint');
    }

    /**
     * Tạo đơn hàng thanh toán ZaloPay
     */
    public function createOrder($orderId, $amount, $description = '')
    {
        $appTransId = date('ymd') . '_' . $orderId . '_' . Str::random(6);
        $amount = (int) $amount; // ZaloPay yêu cầu amount là số nguyên

        $params = [
            'app_id' => $this->appId,
            'app_trans_id' => $appTransId,
            'app_user' => 'user_' . $orderId,
            'amount' => $amount,
            'app_time' => time() * 1000, // ZaloPay yêu cầu timestamp tính bằng milliseconds
            'embed_data' => json_encode([
                'order_id' => $orderId,
                'description' => $description
            ]),
            'item' => json_encode([
                [
                    'itemid' => $orderId,
                    'itemname' => $description,
                    'itemprice' => $amount,
                    'itemquantity' => 1
                ]
            ]),
            'description' => $description,
            'bank_code' => 'zalopayapp',
            'callback_url' => $this->callbackUrl,
            'redirect_url' => $this->redirectUrl
        ];

        // Tạo chữ ký
        $data = $params['app_id'] . '|' . $params['app_trans_id'] . '|' . $params['app_user'] . '|' . $params['amount'] . '|' . $params['app_time'] . '|' . $params['embed_data'] . '|' . $params['item'];
        $params['mac'] = hash_hmac('sha256', $data, $this->key1);

        try {
            $response = Http::asForm()->post($this->endpoint . '/create', $params);
            $result = $response->json();

            Log::info('ZaloPay Create Order Response', [
                'order_id' => $orderId,
                'app_trans_id' => $appTransId,
                'response' => $result
            ]);

            if ($result && $result['return_code'] == 1) {
                return [
                    'success' => true,
                    'app_trans_id' => $appTransId,
                    'zalopay_trans_id' => $result['zp_trans_token'] ?? null,
                    'order_url' => $result['order_url'],
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'message' => $result['return_message'] ?? 'Có lỗi xảy ra khi tạo đơn hàng',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('ZaloPay Create Order Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi kết nối đến ZaloPay: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kiểm tra trạng thái thanh toán
     */
    public function getOrderStatus($appTransId)
    {
        $params = [
            'app_id' => $this->appId,
            'app_trans_id' => $appTransId
        ];

        // Tạo chữ ký
        $data = $params['app_id'] . '|' . $params['app_trans_id'] . '|' . $this->key1;
        $params['mac'] = hash_hmac('sha256', $data, $this->key1);

        try {
            $response = Http::asForm()->post($this->endpoint . '/query', $params);
            $result = $response->json();

            Log::info('ZaloPay Query Order Response', [
                'app_trans_id' => $appTransId,
                'response' => $result
            ]);

            if ($result && $result['return_code'] == 1) {
                return [
                    'success' => true,
                    'status' => $result['status'],
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'message' => $result['return_message'] ?? 'Có lỗi xảy ra khi kiểm tra trạng thái',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('ZaloPay Query Order Error', [
                'app_trans_id' => $appTransId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi kết nối đến ZaloPay: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Xác thực callback từ ZaloPay
     */
    public function verifyCallback($data)
    {
        $mac = $data['mac'] ?? '';
        unset($data['mac']);

        // Sắp xếp các tham số theo thứ tự alphabet
        ksort($data);
        $dataString = '';
        foreach ($data as $key => $value) {
            $dataString .= $key . '=' . $value . '&';
        }
        $dataString = rtrim($dataString, '&');

        $macCalculated = hash_hmac('sha256', $dataString, $this->key2);

        return hash_equals($mac, $macCalculated);
    }

    /**
     * Xử lý callback từ ZaloPay
     */
    public function handleCallback($data)
    {
        if (!$this->verifyCallback($data)) {
            Log::warning('ZaloPay Callback Verification Failed', ['data' => $data]);
            return [
                'success' => false,
                'message' => 'Chữ ký không hợp lệ'
            ];
        }

        $appTransId = $data['app_trans_id'] ?? '';
        $status = $data['status'] ?? 0;

        Log::info('ZaloPay Callback Received', [
            'app_trans_id' => $appTransId,
            'status' => $status,
            'data' => $data
        ]);

        return [
            'success' => true,
            'app_trans_id' => $appTransId,
            'status' => $status,
            'data' => $data
        ];
    }

    /**
     * Hoàn tiền (refund)
     */
    public function refund($zalopayTransId, $amount, $description = '')
    {
        $params = [
            'app_id' => $this->appId,
            'zp_trans_id' => $zalopayTransId,
            'amount' => (int) $amount,
            'description' => $description,
            'timestamp' => time() * 1000
        ];

        // Tạo chữ ký
        $data = $params['app_id'] . '|' . $params['zp_trans_id'] . '|' . $params['amount'] . '|' . $params['description'] . '|' . $params['timestamp'];
        $params['mac'] = hash_hmac('sha256', $data, $this->key1);

        try {
            $response = Http::asForm()->post($this->endpoint . '/refund', $params);
            $result = $response->json();

            Log::info('ZaloPay Refund Response', [
                'zalopay_trans_id' => $zalopayTransId,
                'response' => $result
            ]);

            if ($result && $result['return_code'] == 1) {
                return [
                    'success' => true,
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'message' => $result['return_message'] ?? 'Có lỗi xảy ra khi hoàn tiền',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('ZaloPay Refund Error', [
                'zalopay_trans_id' => $zalopayTransId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi kết nối đến ZaloPay: ' . $e->getMessage()
            ];
        }
    }
}
