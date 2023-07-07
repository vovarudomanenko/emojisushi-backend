<?php

namespace Layerok\PosterPos\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Layerok\PosterPos\Models\Settings;
use Maksa988\WayForPay\Facades\WayForPay;
use Telegram\Bot\Api;
use WayForPay\SDK\Domain\TransactionService;

class WayForPayController {
    public $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function __invoke() {
        $content = $this->request->getContent();
        $data = json_decode($content);

        return WayForPay::handleServiceUrl($data, function (TransactionService $transaction, $success) use($data) {
            $order_number = $transaction->getOrderReference();
            $amount = $transaction->getAmount();
            $currency = $transaction->getCurrency();
            $status = $transaction->getStatus();

            $token =  Settings::get('telegram_bot_token');
            $chat_id = Settings::get('telegram_chat_id');
            $api = new Api($token);

            if($transaction->getReason()->isOK()) {
                if($transaction->isStatusApproved()) {
                    $message = "✅ Успішний платіж на сайті https://emojisushi.com.ua \n\nСума: $amount $currency \nНомер замовлення: $order_number";
                } else if($transaction->isStatusRefunded()) {
                    $message = "Платіж повернуто \nСума: $amount $currency \nНомер замовлення: $order_number";
                } else if ($transaction->isStatusDeclined()) {
                    $message = "Платіж скасовано \nСума: $amount $currency \nНомер замовлення: $order_number";
                } else if ($transaction->isStatusExpired()) {
                    $message = "Час на оплату вичерпано \nСума: $amount $currency \nНомер замовлення: $order_number";
                } else {
                    $message = "Статус платежу: $status \nСума: $amount $currency \nНомер замовлення: $order_number";
                }
                $api->sendMessage([
                    'text' => $message,
                    'parse_mode' => "html",
                    'chat_id' => $chat_id
                ]);
                Log::channel('single')->debug('WayForPay transaction №' . $order_number . 'is ' . $status);
                return $success();
            }

            $error = "[Error] WayForPay transaction №". $order_number . ":" . $transaction->getReason()->getCode().  $transaction->getReason()->getMessage();
            Log::error($error);

            return $error;
        });
    }
}
