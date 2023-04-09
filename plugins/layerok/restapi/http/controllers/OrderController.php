<?php

namespace Layerok\Restapi\Http\Controllers;

use App\Libraries\Poster;
use App\Models\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Layerok\PosterPos\Classes\PosterProducts;
use Layerok\PosterPos\Classes\PosterUtils;
use Layerok\PosterPos\Models\Spot;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Classes\PaymentState\FailedState;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Classes\PaymentState\PendingState;
use OFFLINE\Mall\Classes\PaymentState\RefundedState;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Wishlist;
use poster\src\PosterApi;
use RainLab\Location\Models\Country;
use Telegram\Bot\Api;
use Layerok\BaseCode\Classes\Receipt;
use WayForPay;
use WayForPay\SDK\Domain\Product;
use Maksa988\WayForPay\Collection\ProductCollection;
use Maksa988\WayForPay\Domain\Client;

class OrderController extends Controller
{
    public $cart = null;
    public function place(): JsonResponse
    {

        $data = post();

        $rules = [
            'phone'             => 'required|phoneUa',
            'email'             => 'email|nullable',
        ];

        $messages = [
            'email.required'          => trans('offline.mall::lang.components.signup.errors.email.required'),
            'email.email'             => trans('offline.mall::lang.components.signup.errors.email.email'),
            'phone.phone_ua'          => trans('layerok.posterpos::lang.validation.phone.ua')
        ];

        $validation = Validator::make($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $shipping_id = $data['shipping_id'];
        $payment_id = $data['payment_id'];

        $shipping = ShippingMethod::where('id', $shipping_id)->first();
        $payment = PaymentMethod::where('id', $payment_id)->first();

        $this->cart = Cart::bySession();
        $this->cart->setPaymentMethod($payment);
        $this->cart->setShippingMethod($shipping);

        $customer = Customer::where('phone', $data['phone'])->first();
        if(!$customer) {
            $user = $this->registerGuest([
                'firstname' => optional($data)['first_name'],
                'lastname' => optional($data)['last_name'],
                'email' => optional($data)['email']
            ]);
            $user->customer->phone = $data['phone'];
            $user->customer->save();
        } else {
            Cart::transferSessionCartToCustomer($customer);
            Wishlist::transferToCustomer($customer);
        }

        $this->cart->refresh();

        $order = Order::fromCart($this->cart);

        $products = $this->cart->products()->get();

        $way_products = [];

        if($payment->code === 'wayforpay') {
            foreach ($products as $cartProduct) {
                $way_products[] = new Product($cartProduct->product->name, ($cartProduct->price()->price / 100) * $cartProduct->quantity, $cartProduct->quantity);
            }
        }

        if (!count($products) > 0) {
            throw new ValidationException(['Ваш заказ пустой. Пожалуйста добавьте товар в корзину.']);
        }

        $spot = $this->getSelectedSpot();

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
            'payment_method_name' => $payment->name,
            'delivery_method_name' => $shipping->name
        ], function($key) {
            return $this->t($key);
        });


        $tablet_id = $spot->tablet->tablet_id ?? env('POSTER_FALLBACK_TABLET_ID');

        // todo: id for website, wayforpay, posterpos order must be the same

        if(env('POSTER_SEND_ORDER_ENABLED')) {
            PosterApi::init();
            $result = (object)PosterApi::incomingOrders()
                ->createIncomingOrder([
                    'spot_id' => $tablet_id,
                    'phone' => $data['phone'],
                    'address' => $data['address'] ?? "",
                    'comment' => $poster_comment,
                    'products' => $posterProducts->all(),
                    'first_name' => $data['first_name'] ?? "",
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
            ->headline($this->t('new_order') . " #" . $order->order_number)
            ->field('first_name', optional($data)['first_name'])
            ->field('last_name', optional($data)['last_name'])
            ->field('phone', $data['phone'])
            ->field('delivery_method_name', optional($shipping)->name)
            ->field('address', optional($data)['address'])
            ->field('payment_method_name', optional($payment)->name)
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
        if($payment->code === 'wayforpay') {
            $way_products = new ProductCollection($way_products);
            $client = new Client(
                optional($data)['first_name'],
                optional($data)['last_name'],
                optional($data)['email'],
                optional($data)['phone']
            );

            $form = WayForPay::purchase(
                $order->order_number,
                1,
                $client,
                $way_products,
                'UAH', null, 'UA', null,
                env('WAYFORPAY_RETURN_URL'),
                env('WAYFORPAY_SERVICE_URL')
            )->getAsString($submitText = 'Pay', $buttonClass = 'btn btn-primary'); // Get html form as string

            return response()->json([
                'form' => $form
            ]);
        }


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

    public function registerGuest($data) {
        $modified_data = $data;

        // mocking some values if they weren't provided in order to be able to create user
        if(empty($data['firstname'])) {
            $modified_data['firstname'] = 'Гість';
        }
        if(empty($data['lastname'])) {
            $modified_data['lastname'] = date('Y-m-d_His');
        }
        if(empty($data['email'])) {
            $modified_data['email'] = 'mall-guest'. date('Y-m-d_His'). '@hmail.com';
        }

        $modified_data['terms_accepted'] = true;
        $modified_data['billing_lines'] = 'Адреса';
        $modified_data['billing_city'] = 'Одеса';
        $modified_data['billing_zip'] = '65125';
        $modified_data['billing_country_id'] =  Country::where('code', 'UA')->first()->id;;


        $user = app(SignUpHandler::class)->handle($modified_data, true);
        if ( ! $user) {
            throw new ValidationException(
                [trans('offline.mall::lang.components.quickCheckout.errors.signup_failed')]
            );
        }
        return $user;
    }

    public function t($key) {
        return \Lang::get('layerok.tgmall::lang.telegram.receipt.' . $key);
    }

    public function handle(Request $request){
        Log::channel('single')->debug('wayforpay прислал ответ');

        $content = $request->getContent();
        $data = json_decode($content);
        Log::info(json_encode($data));

        return WayForPay::handleServiceUrl($data, function (WayForPay\SDK\Domain\TransactionService $transaction, $success) {
            $order_number = $transaction->getOrderReference();
            $order = Order::where('order_number' , $order_number)->first();

            if(!$order) {
                Log::error('Заказ №' . $order_number . ' не найден при обработке ответа от wayforpay');
                return $success();
            }

            if($transaction->isStatusApproved()) {
                $order->payment_state = PaidState::class;
            }else if($transaction->isStatusPending()) {
                $order->payment_state = PendingState::class;
            }else if($transaction->isStatusRefunded()) {
                $order->payment_state = RefundedState::class;
            } else {
                // todo: handle all wayforpay statuses
                // but for time being all not existing statuses will be marked as failed
                $order->payment_state = FailedState::class;
            }
            $order->save();
            if(!$transaction->getReason()->isOK()) {
                $error = "Ошибка при оплате заказа №". $order_number . ":" . $transaction->getReason()->getMessage();
                Log::error($error);

                return $error;
            } else {

                return $success();
            }
        });

    }


}
