
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kind Society | Sign up</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
          crossorigin="anonymous">

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

            <span class="auth-kicker">JOIN OUR COMMUNITY</span>

            <h1 class="h3 mb-3 fw-normal">Create your account</h1>

            <p class="auth-subtitle">
                Start making a difference today.
            </p>

            <!-- Full name -->
            <div class="form-floating mb-3">
                <input type="text"
                       class="form-control"
                       id="emri"
                       name="emri"
                       placeholder="Full name"
                       required>

                <label for="emri">Full name</label>
            </div>

            <!-- Username -->
            <div class="form-floating mb-3">
                <input type="text"
                       class="form-control"
                       id="username"
                       name="username"
                       placeholder="Username"
                       required>

                <label for="username">Username</label>
            </div>

            <!-- Email -->
            <div class="form-floating mb-3">
                <input type="email"
                       class="form-control"
                       id="email"
                       name="email"
                       placeholder="Email address"
                       required>

                <label for="email">Email address</label>
            </div>

            <!-- Password -->
            <div class="form-floating mb-3">
                <input type="password"
                       class="form-control"
                       id="password"
                       name="password"
                       placeholder="Password"
                       required>

                <label for="password">Password</label>
            </div>

            <!-- Confirm password -->
            <div class="form-floating mb-3">
                <input type="password"
                       class="form-control"
                       id="confirm_password"
                       name="confirm_password"
                       placeholder="Confirm password"
                       required>

                <label for="confirm_password">Confirm password</label>
            </div>

            <!-- Account type -->
            <div class="form-floating mb-3">
                <select class="form-select"
                        id="role"
                        name="role"
                        required>

                    <option value="donor" selected>Donor</option>
                    <option value="charity">Charity</option>
                    <option value="admin">Admin</option>

                </select>

                <label for="role">Account type</label>
            </div>

            <!-- Admin registration code -->
            <div class="form-floating mb-3"
                 id="adminCodeField"
                 style="display: none;">

                <input type="password"
                       class="form-control"
                       id="admin_code"
                       name="admin_code"
                       placeholder="Admin registration code">

                <label for="admin_code">Admin registration code</label>
            </div>

            <!-- Charity information -->
            <div id="charityFields" style="display: none;">

                <h5 class="text-start mb-3">Charity Information</h5>

                <!-- Charity name -->
                <div class="form-floating mb-3">
                    <input type="text"
                           class="form-control"
                           id="charity_name"
                           name="charity_name"
                           placeholder="Charity name">

                    <label for="charity_name">Charity Name</label>
                </div>

                <!-- Description -->
                <div class="form-floating mb-3">
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              placeholder="Description"
                              style="height: 110px;"></textarea>

                    <label for="description">Description</label>
                </div>

                <!-- City -->
                <div class="form-floating mb-3">
                   <select name="city" id="city" class="form-select" required>
    <option value="" selected disabled>Select your city</option>
    <option value="Deçan">Deçan</option>
    <option value="Dragash">Dragash</option>
    <option value="Drenas">Drenas</option>
    <option value="Ferizaj">Ferizaj</option>
    <option value="Fushë Kosovë">Fushë Kosovë</option>
    <option value="Gjakovë">Gjakovë</option>
    <option value="Gjilan">Gjilan</option>
    <option value="Graçanicë">Graçanicë</option>
    <option value="Hani i Elezit">Hani i Elezit</option>
    <option value="Istog">Istog</option>
    <option value="Junik">Junik</option>
    <option value="Kaçanik">Kaçanik</option>
    <option value="Kamenicë">Kamenicë</option>
    <option value="Klinë">Klinë</option>
    <option value="Kllokot">Kllokot</option>
    <option value="Leposaviq">Leposaviq</option>
    <option value="Lipjan">Lipjan</option>
    <option value="Malishevë">Malishevë</option>
    <option value="Mamushë">Mamushë</option>
    <option value="Mitrovicë e Jugut">Mitrovicë e Jugut</option>
    <option value="Mitrovicë e Veriut">Mitrovicë e Veriut</option>
    <option value="Novobërdë">Novobërdë</option>
    <option value="Obiliq">Obiliq</option>
    <option value="Partesh">Partesh</option>
    <option value="Pejë">Pejë</option>
    <option value="Podujevë">Podujevë</option>
    <option value="Prishtinë">Prishtinë</option>
    <option value="Prizren">Prizren</option>
    <option value="Rahovec">Rahovec</option>
    <option value="Ranillug">Ranillug</option>
    <option value="Shtime">Shtime</option>
    <option value="Shtërpcë">Shtërpcë</option>
    <option value="Skenderaj">Skenderaj</option>
    <option value="Suharekë">Suharekë</option>
    <option value="Viti">Viti</option>
    <option value="Vushtrri">Vushtrri</option>
    <option value="Zubin Potok">Zubin Potok</option>
    <option value="Zvečan">Zvečan</option>
</select>

                    <label for="city">City</label>
                </div>

            </div>

            <!-- Remember me -->
            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" value="remember-me">
                    Remember me
                </label>
            </div>

            <!-- Submit -->
            <button class="w-100 btn btn-lg btn-primary"
                    type="submit"
                    name="submit">

                Sign up

            </button>

            <p class="mt-3">
                Already have an account?
                <a href="login.php">Sign in</a>
            </p>

        </form>

    </main>

</div>

<script>
    const roleSelect = document.getElementById('role');

    const adminCodeField = document.getElementById('adminCodeField');
    const adminCodeInput = document.getElementById('admin_code');

    const charityFields = document.getElementById('charityFields');
    const charityName = document.getElementById('charity_name');
    const description = document.getElementById('description');
    const city = document.getElementById('city');

    function updateRegistrationFields() {

        const isAdmin = roleSelect.value === 'admin';
        const isCharity = roleSelect.value === 'charity';

        // Admin fields
        adminCodeField.style.display = isAdmin ? 'block' : 'none';
        adminCodeInput.required = isAdmin;

        if (!isAdmin) {
            adminCodeInput.value = '';
        }

        // Charity fields
        charityFields.style.display = isCharity ? 'block' : 'none';

        charityName.required = isCharity;
        description.required = isCharity;
        city.required = isCharity;

        if (!isCharity) {
            charityName.value = '';
            description.value = '';
            city.value = '';
        }
    }

    roleSelect.addEventListener('change', updateRegistrationFields);

    updateRegistrationFields();
</script>

</body>
</html>
