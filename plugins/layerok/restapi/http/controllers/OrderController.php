<?php

namespace Layerok\Restapi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Layerok\Basecode\Classes\Receipt;
use Layerok\PosterPos\Classes\ServiceMode;
use Layerok\PosterPos\Classes\ShippingMethodCode;
use Layerok\PosterPos\Models\Cart;
use Layerok\PosterPos\Models\CartProduct;
use Layerok\PosterPos\Models\Spot;
use Maksa988\WayForPay\Facades\WayForPay;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\PaymentMethod;
use Layerok\PosterPos\Models\ShippingMethod;
use OFFLINE\Mall\Models\Product;
use poster\src\PosterApi;
use Telegram\Bot\Api;
use WayForPay\SDK\Domain\Product as WayForPayProduct;
use Maksa988\WayForPay\Collection\ProductCollection;
use Maksa988\WayForPay\Domain\Client;

class OrderController extends Controller
{
    public function place(): JsonResponse
    {
        $data = post();
        $this->validate($data);

        $jwtGuard = app('JWTGuard');

        $user = $jwtGuard->user();
        $cart = Cart::byUser($user);

        if (!$cart->products()->get()->count()) {
            throw new ValidationException([trans('layerok.restapi::validation.cart_empty')]);
        }

        $spotSlug = input('spot_id_or_slug');
        $spot = Spot::findBySlugOrId($spotSlug);

        if(!$spot) {
            // todo: throw validation error
            $spots = Spot::all();
            $spot = $spots->first();
        }

        $shippingMethod = ShippingMethod::where('id', $data['shipping_method_id'])->first();
        $paymentMethod = PaymentMethod::where('id', $data['payment_method_id'])->first();

        if (intval($data['sticks']) > 0) {
            $sticks = Product::where('poster_id', 492)->first();
            $cart->addProduct($sticks, $data['sticks']);
        }

        $posterProducts = $cart->products()->get()->map(function(CartProduct $cartProduct) {
            $item = [];
            $product = $cartProduct->product()->first();
            $item['name'] = $product['name'];
            $item['count'] = $cartProduct['quantity'];
            $item['product_id'] = $product['poster_id'];
            if (isset($cartProduct['variant_id'])) {
                $variant = $cartProduct->getItemDataAttribute();
                $item['modificator_id'] = $variant['poster_id'];
            }
            return $item;
        });

        PosterApi::init(config('poster'));

        $posterComment = "";

        if (!empty($data['comment'])) {
            $posterComment .= sprintf("%s || ", $data['comment']);
        }

        if (!empty($data['change'])) {
            $posterComment .= sprintf("Підготувати решту з: %s || ", $data['change']);
        }

        $posterComment .=  sprintf("Спосіб оплати: %s", $paymentMethod->name);

        $incomingOrder = [
            'spot_id' => $spot->tablet->tablet_id ?? env('POSTER_FALLBACK_TABLET_ID'),
            'phone' => $data['phone'],
            'comment' => $posterComment,
            'products' => $posterProducts,
            'first_name' => $data['firstname'] ?? null,
            'last_name' => $data['lastname'] ?? null,
            'service_mode' => ServiceMode::ON_SITE,
        ];

        if($shippingMethod->code === ShippingMethodCode::COURIER) {
            $incomingOrder['service_mode'] = ServiceMode::COURIER;
            $incomingOrder['address'] = $data['address'] ?? null;
        }

        if($shippingMethod->code === ShippingMethodCode::TAKEAWAY) {
            $incomingOrder['service_mode'] = ServiceMode::TAKEAWAY;
        }

        $posterResult = (object)PosterApi::incomingOrders()
            ->createIncomingOrder($incomingOrder);

        if(isset($posterResult->error)) {
            $key = 'layerok.restapi::lang.poster.errors.' . $posterResult->error;
            if(\Lang::has($key)) {
                $err_text = \Lang::get(
                    'layerok.restapi::lang.poster.errors.' . $posterResult->error
                );
            } else {
                $err_text =
                    $posterResult->message;
            }

            throw new ValidationException([
                $posterResult->error => $err_text
            ]);
        }

        $receiptProducts = $cart->products()->get()->map(function (CartProduct $cartProduct) {
            $item = [];
            $product = $cartProduct->product()->first();
            $item['name'] = $product['name'];
            $item['count'] = $cartProduct['quantity'];
            return $item;
        });

        $token = optional($spot->bot)->token ?? env('TELEGRAM_FALLBACK_BOT_TOKEN');
        $chat_id = optional($spot->chat)->internal_id ?? env('TELEGRAM_FALLBACK_CHAT_ID');
        $api = new Api($token);

        $money = app()->make(Money::class);
        $receipt = new Receipt();

        $receipt
            ->headline('Нове замовлення')
            ->field('first_name', $data['firstname'] ?? null)
            ->field('last_name', $data['lastname'] ?? null)
            ->field('phone', $data['phone'])
            ->field('delivery_method_name', $shippingMethod->name)
            ->field('address', $data['address'])
            ->field('payment_method_name', $paymentMethod->name)
            ->field('change', $data['change'] ?? null)
            ->field('comment', $data['comment'] ?? null)
            ->newLine()
            ->b('order_items')
            ->colon()
            ->newLine()
            ->map($receiptProducts, function($item) {
                $this->product(
                    $item['name'],
                    $item['count']
                )->newLine();
            })
            ->newLine()
            ->field('total', $money->format(
                $cart->totals()->totalPostTaxes(),
                null,
                Currency::$defaultCurrency
            ))
            ->field('spot', $spot->name)
            ->field('target', 'site');


        $api->sendMessage([
            'text' => $receipt->getText(),
            'parse_mode' => "html",
            'chat_id' => $chat_id
        ]);

        if($paymentMethod->code === 'wayforpay') {
            $way_products = $cart->products()->get()->map(function(CartProduct $cartProduct) {
                return new WayForPayProduct(
                    $cartProduct->product->name,
                    ($cartProduct->price()->price / 100),
                    $cartProduct->quantity
                );
            });

            $way_products = new ProductCollection($way_products);
            $client = new Client(
                optional($data)['first_name'],
                optional($data)['last_name'],
                optional($data)['email'],
                optional($data)['phone']
            );
            $total = $cart->totals()->totalPostTaxes() / 100;

            $order_id = $posterResult->response->incoming_order_id + 4324234234;

            $form = WayForPay::purchase(
                $order_id, $total, $client, $way_products,
                'UAH', null, 'UA', null,
                config('wayforpay.returnUrl') . "?order_id={$order_id}",
                config('wayforpay.serviceUrl') . "?spot_id={$spot->id}",

            )->getAsString(); // Get html form as string
            $cart->delete();
            return response()->json([
                'success' => true,
                'form' => $form,
                'poster_order' => $posterResult->response
            ]);
        }
        $cart->delete();

        return response()->json([
            'success' => true,
            'poster_order' => $posterResult->response
        ]);
    }

    public function validate($data) {
        $rules = [
            'phone'             => 'required|phoneUa',
            'firstname'         => 'min:2|nullable',
            'lastname'          => 'min:2|nullable',
            'email'             => 'email|nullable',
            'shipping_method_id' => 'exists:offline_mall_shipping_methods,id',
            'payment_method_id' => 'exists:offline_mall_payment_methods,id'
        ];

        if(isset($data['shipping_method_id'])) {
            $shippingMethod = ShippingMethod::where('id', $data['shipping_method_id'])->first();
            if($shippingMethod) {
                if($shippingMethod->code === 'courier') {
                    $rules['address'] = 'required';
                    $messages['address.required'] = trans('layerok.restapi::validation.address_required');
                }
            }
        }

        $messages = [
            'email.required'          => trans('offline.mall::lang.components.signup.errors.email.required'),
            'email.email'             => trans('offline.mall::lang.components.signup.errors.email.email'),
            'phone.phone_ua'          => trans('layerok.posterpos::lang.validation.phone.ua'),
            'email.non_existing_user' => trans('layerok.restapi::validation.customer_exists'),
            'shipping_method_id' => trans('layerok.restapi::validation.shipping_method_exists'),
            'payment_method_id' => trans('layerok.restapi::validation.payment_method_exists'),
            'firstname.min' => trans('layerok.restapi::validation.firstname_min'),
            'lastname.min' => trans('layerok.restapi::validation.lastname_min'),
        ];

        $validation = Validator::make($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }
    }

}
