<?php
return [
    'errors' => [
        'auth_user_false' => 'Требуется повторная авторизация. Пожалуйста, войдите в систему заново',
        'auth_token_mismatch' => 'Время сессии истекло. Пожалуйста, войдите в систему заново',
        'auth_invalid_credentials' => 'Неверные учетные данные',
        'auth_token_error' => 'Не авторизован',
        'register_for_more_features' => 'Чтобы получить больше возможностей на нашем сайте, пройдите регистрацию',
    ],
    'labels' => [
        'guest' => 'Гость',
    ],
    'rules' => [
        'registration_disabled' => 'Регистрация отключена',
    ],
    'validation' => [
        'first_name_required' => 'Имя обязательно',
        'first_name_string' => 'Имя должно быть строкой',
        'first_name_max'    => 'Имя не должно превышать 255 символов',
        'last_name_required' => 'Фамилия обязательна',
        'last_name_string'  => 'Фамилия должна быть строкой',
        'last_name_max'     => 'Фамилия не должна превышать 255 символов',
        'base_name_required' => 'Название базы обязательно',
        'base_name_string'  => 'Название базы должно быть строкой',
        'base_name_max'     => 'Название базы не должно превышать 255 символов',

        'role_required' => 'Роль обязательна',
        'role_invalid'  => 'Недопустимая роль',

        'phone_required' => 'Телефон обязателен',
        'phone_unique'   => 'Такой телефон уже существует',

        'email_required' => 'Email обязателен',
        'email_invalid'  => 'Неверный формат email',
        'email_max'      => 'Email не должен превышать 255 символов',
        'email_unique'   => 'Этот email уже занят',

        'password_required' => 'Пароль обязателен',
        'password_string'   => 'Пароль должен быть строкой',
        'password_min_login' => 'Пароль должен быть минимум 6 символов',
        'password_min'      => 'Пароль должен быть минимум 8 символов',
        'password_upper_lower' => 'Пароль должен содержать заглавные и строчные буквы',
        'password_number' => 'Пароль должен содержать хотя бы одну цифру',
        'password_compromised' => 'Этот пароль найден в утечках данных. Выберите другой пароль.',

        'term_required'  => 'Необходимо согласие с условиями',
        'term_accepted'  => 'Необходимо принять условия',
    ],
    'successes' => [
        'register_successfully' => 'Вы успешно зарегистрированы',
    ]
];
