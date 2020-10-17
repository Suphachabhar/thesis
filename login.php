<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Login</h2>
        </div>
        <form action="login.php" method="post">
            <div>
                <label for="username">Username: </label>
                <input type="text" name="username">
            </div>

            <div>
                <label for="password">Password: </label>
                <input type="password" name="password_1">
            </div>

            <button type="submit">Login</button>
            <p> Not a user? <a href="registration.php"><b>register</b></a></p>
        </form>
    </div>
</body>
</html>