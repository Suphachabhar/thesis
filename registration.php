<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Register</h2>
        </div>
        <form action="registeration.php" method="post">
            <div>
                <label for="username">Username: </label>
                <input type="text" name="username">
            </div>

            <div>
                <label for="email">Email: </label>
                <input type="email" name="email">
            </div>

            <div>
                <label for="password">Password: </label>
                <input type="password" name="password_1">
            </div>

            <div>
                <label for="password">Confirm Password: </label>
                <input type="password" name="password_2">
            </div>

            <button type="submit">Submit</button>
            <p> Already a user? <a href="login.php"><b>log in</b></a></p>
        </form>
    </div>
</body>
</html>