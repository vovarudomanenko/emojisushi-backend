<?php

return [
    'plugin' => [
        'name' => 'Бот telegram магазина',
        'description' => 'Позволь своим покупателям осуществлят заказы в своем любимом месседжере'
    ],
    'telegram' => [
        'buttons' => [
            "plus" => "➕",
            "minus" => "➖",
            "del" => "❌",
            "load_more" => "Загрузить еще",
            "added_to_cart" => "✅ В корзине",
            "categories" => "🍱 Меню",
            "cart" => "🛒 Корзина",
            "add_to_cart" => "Добавить в корзину",
            "in_menu_main" => "🏠 На главную",
            "chose_branch" => "Выберите заведение",
            "contact" => "☎ Контакты",
            "take_order" => "✅ Оформить заказ",
            "to_categories" => "🔙 В меню",
            "cancel" => "Отменить",
            "price" => "Цена",
        ],

        'texts' => [
            "welcome" => "%s, добро пожаловать в наш чат-бот!\n\nЧтобы сделать заказ, нажмите пожалуйста на 🍱 Меню.",
            "category" => "Выберите пожалуйста блюда которые вы хотите заказать",
            "cart_is_empty" => "Ваша корзина пуста.",
            "all_amount_order" => "🧾 Сумма заказа: :price",
            "triple_dot" => "Ниже вы можете перейти в корзину или вернуться на главную",
            "thank_you" => "Спасибо за Ваш заказ, мы свяжемся с Вами в ближайшее время!",
            "new_order" => "Новый заказ",
            "cart" => "⬇️ Ниже, вы можете просмотреть сумму заказа, оформить заказ либо вернуться на главную.",
            "payment_change" =>  "Приготовить сдачу с",
            "chose_delivery_method" => "Выберите тип доставки",
            "chose_payment_method" => "Выберите тип оплаты",
            "right_phone_number" => "Верный номер телефона",
            "prepare_change_question" => "Желаете, чтобы мы подготовили сдачу?",
            "leave_comment_question" => "Желаете оставить комментарий?",
            "confirm_order_question" => "Подтверждаете заказ?",
            "add_sticks_question" => "Желаете добавить палочки к заказу?",
            "type_delivery_address" => "Введите адрес доставки",
        ],

        'receipt' => [
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'phone' => 'Тел',
            'email' => 'Почта',
            'comment' => 'Комментарий',
            'address' => 'Адрес доставки',
            'products' => 'Товары',
            'total'   => 'Итого',
            'delivery_method_name'   => 'Доставка',
            'change'   => 'Приготовить сдачу с',
            'payment_method_name'   => 'Оплата',
            'payment_status'   => 'Статус оплаты',
            'spot_name' => 'Заведение'
        ]
    ],

];
