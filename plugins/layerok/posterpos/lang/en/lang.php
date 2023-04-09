<?php
return [
    'extend' => [
        'poster_id' => 'Poster ID',
        'published' => 'Published'
    ],
    'validation' => [
        'phone' => [
            'ua' => "Не верный формат украинского номера",
            'required' => "Телефон обязателен для заполения"
        ] ,
    ]
];
