<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
</head>
<body>
        <h2>
            Your access in the app {{ $data['app'] }}
        </h2>
        <br>
        <p>Login: {{ $data['user']->login }}</p>
        <p>Password: {{ $data['password'] }}</p>

</body>
</html>
