<?php

namespace Layerok\Restapi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Layerok\PosterPos\Classes\PosterProducts;
use Layerok\PosterPos\Classes\PosterUtils;
use Layerok\PosterPos\Models\Cart;
use Layerok\PosterPos\Models\Spot;
use October\Rain\Exception\ValidationException;

use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\User;
use poster\src\PosterApi;
use RainLab\Location\Models\Country;
use Telegram\Bot\Api;
use Layerok\BaseCode\Classes\Receipt;

class OrderController extends Controller
{
    public $cart = null;
    public function place(): JsonResponse
    {

        $data = post();

        $rules = [
            'phone'             => 'required|phoneUa',
            'email'             => 'email|required|non_existing_user',
            'firstname'         => 'required',
            'lastname'          => 'required',
            'address'           => 'required',
            'zip'               => 'required',
            'city'              => 'required',
            'country_code'      => 'required|exists:rainlab_location_countries,code',
            'poster_firstname'  => 'min:2|nullable',
            'poster_lastname'   => 'min:2|nullable',
            'poster_email'      => 'email|nullable',
        ];

        $messages = [
            'email.required'          => trans('offline.mall::lang.components.signup.errors.email.required'),
            'email.email'             => trans('offline.mall::lang.components.signup.errors.email.email'),
            'phone.phone_ua'          => trans('layerok.posterpos::lang.validation.phone.ua'),
            'email.non_existing_user' => trans('layerok.restapi::validation.customer_exists')
        ];

        $validation = Validator::make($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $jwtGuard = app('JWTGuard');
        $user = $jwtGuard->user();

        $this->cart = Cart::byUser($user);
        $spot = $this->getSelectedSpot();

        $products = $this->cart->products()->get();

        if (!count($products) > 0) {
            throw new ValidationException([trans('layerok.restapi::validation.cart_empty')]);
        }

        $shippingMethod = ShippingMethod::where('id', $data['shipping_method_id'])->first();
        $paymentMethod = PaymentMethod::where('id', $data['payment_method_id'])->first();

        if(!$paymentMethod) {
            $paymentMethod = PaymentMethod::getDefault();
        }
        $this->cart->setPaymentMethod($paymentMethod);

        if(!$shippingMethod) {
            $shippingMethod = ShippingMethod::getDefault();
        }
        $this->cart->setShippingMethod($shippingMethod);

        if($shippingMethod->code === 'courier' && empty($data['address'])) {
            throw new ValidationException([
                'address' => 'Заповніть поле "Адреса доставки"'
            ]);
        }

        $user = $this->registerGuestIfNeeded($data, $user);

        $this->createShippingAddress($data, $user, $spot, $shippingMethod, $this->cart);

        $order = Order::fromCart($this->cart);
        $order->spot_id = $spot->id;
        $order->save();

        $posterProducts = new PosterProducts();

        $posterProducts
            ->addCartProducts($products)
            ->addProduct(
                492,
                $this->t('sticks_name'),
                $data['sticks']
            );



        $poster_comment = PosterUtils::getComment([
            'comment' => $data['comment'] ?? null,
            'change' => $data['change'] ?? null,
            'payment_method_name' => $paymentMethod->name,
            'delivery_method_name' => $shippingMethod->name
        ], function($key) {
            return $this->t($key);
        });


        $tablet_id = $spot->tablet->tablet_id ?? env('POSTER_FALLBACK_TABLET_ID');

        if(env('POSTER_SEND_ORDER_ENABLED')) {
            PosterApi::init();
            $result = (object)PosterApi::incomingOrders()
                ->createIncomingOrder([
                    'spot_id' => $tablet_id,
                    'phone' => $data['phone'],
                    'address' => $data['address'] ?? "",
                    'comment' => $poster_comment,
                    'products' => $posterProducts->all(),
                    'first_name' => $data['poster_firstname'] ?? "",
                    'last_name' => $data['poster_lastname'] ?? "",
                ]);

            if(isset($result->error)) {
                $poster_err = \Lang::get(
                    'layerok.restapi::lang.poster.errors.' . $result->error,
                    [],
                    null,
                    $result->message
                );
                throw new ValidationException([
                    $result->error => $poster_err
                ]);
            }
        }

        $token = optional($spot->bot)->token ?? env('TELEGRAM_FALLBACK_BOT_TOKEN');
        $chat_id = optional($spot->chat)->internal_id ?? env('TELEGRAM_FALLBACK_CHAT_ID');
        $api = new Api($token);

        $receipt = $this->getReceipt();
        $receipt
            ->headline($this->t('new_order'))
            ->field('first_name', optional($data)['firstname'])
            ->field('last_name', optional($data)['lastname'])
            ->field('phone', $data['phone'])
            ->field('delivery_method_name', optional($shippingMethod)->name)
            ->field('address', optional($data)['address'])
            ->field('payment_method_name', optional($paymentMethod)->name)
            ->field('change', optional($data)['change'])
            ->field('comment', optional($data)['comment'])
            ->newLine()
            ->products($posterProducts->all())
            ->newLine()
            ->field('total', $this->cart->getTotalFormattedPrice())
            ->field('spot', $spot->name)
            ->field('target', $receipt->trans('site'));

        $api->sendMessage([
            'text' => $receipt->getText(),
            'parse_mode' => "html",
            'chat_id' => $chat_id
        ]);


        $this->cart->delete();



        return response()->json([
            'success' => true,
        ]);
    }

    public function getSelectedSpot() {
        $spots = Spot::all();


        $spot_id = input('spot_id');

        foreach ($spots as $spot) {
            if ($spot_id == $spot['id']) {
                return $spot;
            }
        }

        // По умолчанию будет выбрано первое заведение
        return $spots->first();
    }

    public function getReceipt(): Receipt
    {
        $receipt = new Receipt();

        $receipt->setProductNameResolver(function($product) {
            return $product['name'];
        });
        $receipt->setProductCountResolver(function($product) {
            return $product['count'];
        });

        $receipt->setTransResolver(function($key) {
            return $this->t($key);
        });

        return $receipt;
    }

    public function t($key) {
        return \Lang::get('layerok.tgmall::lang.telegram.receipt.' . $key);
    }

    public function registerGuestIfNeeded($data, ?User $user) {
        if (!$user) {
            // if user is not logged in, then he is a quest
            $user = $this->registerGuest($data);
            $this->cart = $this->cart->refresh();
        }
        return $user;
    }

    public function registerGuest($data) {
        $user = app(SignUpHandler::class)->handle($data, true);
        if ( ! $user) {
            throw new ValidationException(
                [trans('offline.mall::lang.components.quickCheckout.errors.signup_failed')]
            );
        }
        return $user;
    }

    public function createShippingAddress($data, User $user, Spot $spot, ShippingMethod $shippingMethod, Cart $cart) {
        $customer = $user->customer;


        $billing = new Address();

        $billing->name = $customer['firstname'] . ' ' . $customer['lastname'];
        $billing->lines = $data['address'];
        $billing->zip = $data['zip'];
        $billing->city = $data['city'];
        $billing->country_id = Country::where('code', 'UA')->first()->id;;
        $billing->customer_id = $customer->id;
        $billing->save();


        $customer->default_billing_address_id = $billing->id;
        $customer->default_shipping_address_id = $billing->id;

        $customer->save();

        $cart->setShippingAddress($billing);
        $cart->setBillingAddress($billing);
        $cart->save();
    }


}
