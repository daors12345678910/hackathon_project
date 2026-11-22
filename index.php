
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kind Society | Register</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
          crossorigin="anonymous">

    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .form-signin {
            width: 100%;
            max-width: 380px;
            padding: 30px;
            margin: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .form-signin .form-floating {
            margin: 10px 0;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin .form-control,
        .form-signin .form-select {
            border-radius: 7px;
        }

        .form-signin .form-control:focus,
        .form-signin .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .form-signin h1 {
            color: #212529;
            font-weight: 600;
        }

        .form-signin button {
            margin-top: 10px;
            border-radius: 7px;
            padding: 10px;
        }

        .form-signin .checkbox {
            font-weight: 400;
            margin-top: 15px;
        }

        .form-signin a {
            text-decoration: none;
            color: #0d6efd;
        }

        .form-signin a:hover {
            text-decoration: underline;
        }

        .register-footer {
            margin-top: 18px;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .form-signin {
                padding: 22px;
            }
        }
    </style>
</head>

<body class="text-center">

<main class="form-signin">

    <form action="register.php" method="POST">

        <img class="mb-4"
             src="https://getbootstrap.com/docs/5.1/assets/brand/bootstrap-logo.svg"
             alt="Bootstrap logo"
             width="72"
             height="57">

        <h1 class="h3 mb-3 fw-normal">Create Account</h1>

        <!-- Name -->
        <div class="form-floating">
            <input type="text"
                   class="form-control"
                   id="emri"
                   placeholder="Name"
                   name="emri"
                   required>

            <label for="emri">Name</label>
        </div>

        <!-- Username -->
        <div class="form-floating">
            <input type="text"
                   class="form-control"
                   id="username"
                   placeholder="Username"
                   name="username"
                   required>

            <label for="username">Username</label>
        </div>

        <!-- Email -->
        <div class="form-floating">
            <input type="email"
                   class="form-control"
                   id="email"
                   placeholder="Email"
                   name="email"
                   required>

            <label for="email">Email</label>
        </div>

        <!-- Password -->
        <div class="form-floating">
            <input type="password"
                   class="form-control"
                   id="password"
                   placeholder="Password"
                   name="password"
                   required>

            <label for="password">Password</label>
        </div>

        <!-- Confirm Password -->
        <div class="form-floating">
            <input type="password"
                   class="form-control"
                   id="confirm_password"
                   placeholder="Confirm Password"
                   name="confirm_password"
                   required>

            <label for="confirm_password">Confirm Password</label>
        </div>

        <!-- Account Role -->
        <div class="form-floating mb-3">

            <select class="form-select"
                    name="role"
                    id="role"
                    required>

                <option value="donor" selected>Donor</option>
                <option value="charity">Charity</option>
                <option value="admin">Admin</option>

            </select>

            <label for="role">Register as</label>

        </div>

        <!-- Admin Registration Code -->
        <div class="form-floating mb-3"
             id="adminCodeField"
             style="display: none;">

            <input type="password"
                   class="form-control"
                   name="admin_code"
                   id="admin_code"
                   placeholder="Admin registration code">

            <label for="admin_code">Admin registration code</label>

        </div>

        <!-- Remember Me -->
        <div class="checkbox mb-3">

            <label>
                <input name="checkbox"
                       type="checkbox"
                       value="remember-me">

                Remember me
            </label>

        </div>

        <!-- Register Button -->
        <button class="w-100 btn btn-lg btn-primary"
                type="submit"
                name="submit">

            Sign up

        </button>

        <!-- Login Link -->
        <div class="register-footer">

            <span>Already have an account?</span>

            <a href="login.php">Sign in</a>

        </div>

    </form>

</main>

<!-- JavaScript for Admin Code -->
<script>

document.getElementById('role').addEventListener('change', function () {

    const adminField = document.getElementById('adminCodeField');
    const adminCode = document.getElementById('admin_code');

    if (this.value === 'admin') {

        adminField.style.display = 'block';
        adminCode.required = true;

    } else {

        adminField.style.display = 'none';
        adminCode.required = false;
        adminCode.value = '';

    }

});

</script>

</body>
</html>
