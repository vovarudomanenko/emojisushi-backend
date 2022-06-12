<?php

return [
    'plugin' => [
        'name' => 'Бот telegram магазину',
        'description' => 'Дозволь своїм покупцям здійснювати замовлення в своєму улюбленому меседжері'
    ],
    'telegram' => [
        'buttons' => [
            "plus" => "➕",
            "minus" => "➖",
            "del" => "❌",
            "load_more" => "Завантажити ще",
            "added_to_cart" => "✅ В кошику",
            "categories" => "🍱 Меню",
            "cart" => "🛒 Кошик",
            "add_to_cart" => "Додати в кошик",
            "in_menu_main" => "🏠 На головну",
            "chose_branch" => "Виберіть заклад",
            "contact" => "☎ Контакти",
            "take_order" => "✅ Оформити замовлення",
            "to_categories" => "🔙 В меню",
            "cancel" => "Відмінити",
            "price" => "Ціна",
        ],

        'texts' => [
            "welcome" => "%s, Ласкаво просимо в наш чат-бот!\n\nЩоб зробити замовлення, натисніть будь ласка на 🍱 Меню.",
            "category" => "Виберіть будь ласка страви які ви хочете замовити",
            "cart_is_empty" => "Ваш кошик порожній.",
            "all_amount_order" => "🧾 Сума замовлення: :price",
            "triple_dot" => "Нижче ви можете перейти до корзини або повернутися на головну",
            "thank_you" => "Дякуємо за Ваше замовлення, ми зв'яжемося з Вами найближчим часом!",
            "new_order" => "Нове замовлення",
            "cart" => "⬇️ Нижче ви можете переглянути суму замовлення, оформити замовлення або повернутися на головну",
            "payment_change" =>  "Приготувати здачу з",
            "chose_delivery_method" => "Виберіть тип доставки",
            "chose_payment_method" => "Виберіть тип оплати",
            "right_phone_number" => "Вірний номер телефону",
            "prepare_change_question" => "Бажаєте, щоб ми підготували решту?",
            "leave_comment_question" => "Бажаєте залишити коментар?",
            "confirm_order_question" => "Підтверджуєте замовлення?",
            "add_sticks_question" => "Бажаєте додати палички на замовлення?",
            "type_delivery_address" => "Введіть адресу доставки",
        ],

        'receipt' => [
            'first_name' => "Ім'я",
            'last_name' => 'Прізвище',
            'phone' => 'Тел',
            'email' => 'Пошта',
            'comment' => 'Коментар',
            'address' => 'Адреса доставки',
            'products' => 'Товари',
            'total'   => 'Разом',
            'delivery_method_name'   => 'Доставка',
            'change'   => 'Приготувати здачу з',
            'payment_method_name'   => 'Оплата',
            'payment_status'   => 'Статус оплати',
            'spot_name' => 'Заклад'
        ]
    ],

];
