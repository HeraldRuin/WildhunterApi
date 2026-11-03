<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Оплата СБП</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #1a1a1a;
            font-family: Arial, sans-serif;
        }

        main {
            text-align: center;
            padding: 24px;
        }

        img {
            width: 280px;
            height: 280px;
        }

        a {
            display: inline-block;
            margin-top: 16px;
            color: #0b5cab;
        }
    </style>
</head>
<body>
<main>
    <p>Отсканируйте QR-код в приложении банка</p>
    <img src="{{ $image }}" alt="QR СБП">
    @if ($link)
        <div><a href="{{ $link }}" target="_top">Открыть в приложении банка</a></div>
    @endif
</main>
</body>
</html>
