
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kind Society | Create account</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="text-center auth-page">

<div class="auth-layout">

    <aside class="auth-story">

        <a class="auth-brand" href="index.php">
            <span class="brand-mark">K</span> Kind Society
        </a>

        <div class="auth-story-copy">
            <span class="eyebrow">CLOTHING DONATION COMMUNITY</span>

            <h2>Give what you can.<br>Change what you can.</h2>

            <p>
                Connect generous people with local charities and help good
                clothes find a new home.
            </p>
        </div>

        <div class="auth-story-foot">
            A little kindness goes a long way.
        </div>

    </aside>

    <main class="form-signin">

        <form action="register.php" method="post">

            <span class="auth-kicker">JOIN THE COMMUNITY</span>

            <h1 class="h3 mb-3 fw-normal">Create your account</h1>

            <p class="auth-subtitle">
                Start making a difference in your community.
            </p>

            <div class="form-floating mb-3">
                <input type="text" class="form-control"
                       id="emri" placeholder="Emri"
                       name="emri" required>
                <label for="emri">Emri</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control"
                       id="username" placeholder="Username"
                       name="username" required>
                <label for="username">Username</label>
            </div>

            <div class="form-floating mb-3">
                <input type="email" class="form-control"
                       id="email" placeholder="Email"
                       name="email" required>
                <label for="email">Email</label>
            </div>

            <!-- Role selection -->
            <div class="mb-3 text-start">
                <label for="role" class="form-label">
                    Choose your account type
                </label>

                <select class="form-select" name="role"
                        id="role" required onchange="toggleAdminCode()">

                    <option value="">Select your role</option>
                    <option value="donor">Donor</option>
                    <option value="charity">Charity</option>
                    <option value="admin">Admin</option>

                </select>
            </div>

            <!-- Admin registration code -->
            <div class="form-floating mb-3"
                 id="adminCodeGroup" style="display: none;">

                <input type="password" class="form-control"
                       id="admin_code" name="admin_code"
                       placeholder="Admin registration code">

                <label for="admin_code">
                    Admin registration code
                </label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control"
                       id="password" placeholder="Password"
                       name="password" required>
                <label for="password">Password</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control"
                       id="confirm_password"
                       placeholder="Confirm Password"
                       name="confirm_password" required>
                <label for="confirm_password">Confirm Password</label>
            </div>

            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" value="remember-me">
                    Remember me
                </label>
            </div>

            <button class="w-100 btn btn-lg btn-primary"
                    type="submit" name="submit">
                Sign up
            </button>

            <div class="mt-3">
                <span>Already have an account? </span>
                <a href="login.php">Sign in</a>
            </div>

        </form>

    </main>

</div>

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
function toggleAdminCode() {

    const role = document.getElementById('role').value;
    const adminGroup = document.getElementById('adminCodeGroup');
    const adminInput = document.getElementById('admin_code');

    if (role === 'admin') {
        adminGroup.style.display = 'block';
        adminInput.required = true;
    } else {
        adminGroup.style.display = 'none';
        adminInput.required = false;
        adminInput.value = '';
    }
}
</script>

</body>
</html>
