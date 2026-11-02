<?php
session_start();

ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fakebook - Register</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
        }

        .background-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        nav {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px 0;
        }

        nav h1 {
            color: white;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
        }

        .main {
            display: flex;
            justify-content: center;
            color: white;
            padding: 10px;
        }

        form {
            display: grid;
            justify-content: center;
            width: 426px;
            max-width: 100%;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 20px 0 24px;
        }

        .main h2 {
            text-align: center;
            margin-bottom: 6px;
        }

        .field {
            margin: 10px 36px;
        }

        .field h3 {
            margin: 0 0 6px 0;
            font-size: 15px;
            color: black;
        }

        .field input {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        .field input:invalid.touched {
            border-color: #ff6b6b;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 38px;
        }

        .toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 4px;
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #333;
        }

        .toggle-password svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .toggle-password .eye-off {
            display: none;
        }

        .toggle-password.showing .eye {
            display: none;
        }

        .toggle-password.showing .eye-off {
            display: block;
        }

        .gender-options {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .gender-options button {
            padding: 10px;
            border: none;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.5);
            color: #111;
            cursor: pointer;
        }

        .gender-options button.selected {
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.9) inset;
            font-weight: bold;
        }

        .error-text {
            display: block;
            min-height: 16px;
            font-size: 12px;
            color: #ffd2d2;
            margin-top: 4px;
        }

        .server-message {
            margin: 0 36px 10px;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            text-align: center;
        }

        .server-message.success {
            background: rgba(60, 180, 100, 0.35);

        }

        .server-message.failure {
            background: rgba(220, 60, 60, 0.35);

        }

        .sub4 {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .button {
            padding: 10px;
            width: 160px;
            border-radius: 10px;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }

        .button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <?php
    
    include("db.php");

    $errors = [];
    $successMessage = "";
    $failureMessage = "";
    if (isset($_SESSION["failureMessage"])) {
        $failureMessage = $_SESSION["failureMessage"];
        unset($_SESSION["failureMessage"]);
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim(filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS) ?? "");
        $age      = trim(filter_input(INPUT_POST, "age", FILTER_SANITIZE_SPECIAL_CHARS) ?? "");
        $gender   = trim(filter_input(INPUT_POST, "gender", FILTER_SANITIZE_SPECIAL_CHARS) ?? "");
        $email    = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS) ?? "");
        $phone    = trim(filter_input(INPUT_POST, "phone_no", FILTER_SANITIZE_SPECIAL_CHARS) ?? "");
        $password = $_POST["password"] ?? "";
        $confirm  = $_POST["confirm_password"] ?? "";

        if ($username === "") {
            $errors[] = "Please enter a username.";
        }
        if ($age === "" || !ctype_digit($age) || (int)$age < 13 || (int)$age > 120) {
            $errors[] = "Please enter a valid age (13-120).";
        }
        if ($gender === "") {
            $errors[] = "Please select your gender.";
        }
        if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        if ($phone === "" || !preg_match('/^\d{7,15}$/', $phone)) {
            $errors[] = "Please enter a valid phone number.";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($errors)) {

            $check = mysqli_prepare($conn, "SELECT id FROM Users WHERE name = ? OR email = ? LIMIT 1");
            mysqli_stmt_bind_param($check, "ss", $username, $email);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $_SESSION["failureMessage"] = "That username or email is already registered.";
                mysqli_stmt_close($check);
                header("Location: " . $_SERVER["PHP_SELF"]);
                exit;
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO Users (name, age, Gender, email, Phone_no, password) VALUES (?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt, "ssssss", $username, $age, $gender, $email, $phone, $hash);

                if (mysqli_stmt_execute($stmt)) {
                    $successMessage = "You are now registered!";
                } else {
                    $failureMessage = "Something went wrong. Please try again.";
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_stmt_close($check);
        }
    }
    ?>

    <video class="background-video" autoplay muted loop>
        <source src="back.mp4" type="video/mp4">
    </video>

    <nav>
        <h1>Fakebook</h1>
    </nav>

    <div class="main">
        <form id="registerForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
            <h2>Welcome to Fakebook</h2>

            <?php if (!empty($errors)): ?>
                <div class="server-message failure">
                    <?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>
                </div>
            <?php elseif ($successMessage): ?>
                <div class="server-message success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php elseif ($failureMessage): ?>
                <div class="server-message failure"><?php echo htmlspecialchars($failureMessage); ?></div>
            <?php endif; ?>

            <div class="field">
                <h3>Name</h3>
                <input type="text" name="username" id="username" required minlength="2" autocomplete="username">
                <span class="error-text" id="username-error"></span>
            </div>

            <div class="field">
                <h3>Enter your age</h3>
                <input type="number" name="age" id="age" required min="13" max="120">
                <span class="error-text" id="age-error"></span>
            </div>

            <div class="field">
                <h3>Select your gender</h3>
                <div class="gender-options" id="genderOptions">
                    <button type="button" data-value="male">Male</button>
                    <button type="button" data-value="female">Female</button>
                    <button type="button" data-value="other">Others</button>
                </div>

                <input type="hidden" name="gender" id="genderInput" value="">
                <span class="error-text" id="gender-error"></span>
            </div>

            <div class="field">
                <h3>Enter your email</h3>
                <input type="email" name="email" id="email" required autocomplete="email">
                <span class="error-text" id="email-error"></span>
            </div>

            <div class="field">
                <h3>Enter your Phone no.</h3>
                <input type="tel" name="phone_no" id="phone" required pattern="\d{7,15}" autocomplete="tel">
                <span class="error-text" id="phone-error"></span>
            </div>

            <div class="field">
                <h3>Password</h3>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
                    <button type="button" class="toggle-password" data-target="password" aria-label="Show password" aria-pressed="false">
                        <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A10.94 10.94 0 0 1 12 5c7 0 11 7 11 7a18.6 18.6 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
                <span class="error-text" id="password-error"></span>
            </div>

            <div class="field">
                <h3>Confirm Password</h3>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" required autocomplete="new-password">
                    <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Show password" aria-pressed="false">
                        <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A10.94 10.94 0 0 1 12 5c7 0 11 7 11 7a18.6 18.6 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
                <span class="error-text" id="confirm-error"></span>
            </div>

            <div class="sub4">
                <input class="button" type="submit" name="submit" id="submitBtn" value="Register">
            </div>
        </form>
    </div>

    <script>
        (function() {
            const form = document.getElementById("registerForm");
            const fields = {
                username: {
                    el: document.getElementById("username"),
                    error: document.getElementById("username-error")
                },
                age: {
                    el: document.getElementById("age"),
                    error: document.getElementById("age-error")
                },
                email: {
                    el: document.getElementById("email"),
                    error: document.getElementById("email-error")
                },
                phone: {
                    el: document.getElementById("phone"),
                    error: document.getElementById("phone-error")
                },
                password: {
                    el: document.getElementById("password"),
                    error: document.getElementById("password-error")
                },
                confirm_password: {
                    el: document.getElementById("confirm_password"),
                    error: document.getElementById("confirm-error")
                }
            };
            const genderError = document.getElementById("gender-error");
            const genderButtons = document.querySelectorAll("#genderOptions button");
            const genderInput = document.getElementById("genderInput");
            const submitBtn = document.getElementById("submitBtn");

            function setError(field, message) {
                field.el.classList.add("touched");
                field.error.textContent = message;
            }

            function clearError(field) {
                field.error.textContent = "";
            }

            function validateUsername() {
                const v = fields.username.el.value.trim();
                if (v.length < 2) {
                    setError(fields.username, "Username must be at least 2 characters.");
                    return false;
                }
                clearError(fields.username);
                return true;
            }

            function validateAge() {
                const v = Number(fields.age.el.value);
                if (!fields.age.el.value || v < 13 || v > 120) {
                    setError(fields.age, "Age must be between 13 and 120.");
                    return false;
                }
                clearError(fields.age);
                return true;
            }

            function validateGender() {
                const picked = genderInput.value !== "";
                genderError.textContent = picked ? "" : "Please select a gender.";
                return picked;
            }

            function validateEmail() {
                const v = fields.email.el.value.trim();
                const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
                setError(fields.email, ok ? "" : "Enter a valid email address.");
                return ok;
            }

            function validatePhone() {
                const v = fields.phone.el.value.trim();
                const ok = /^\d{7,15}$/.test(v);
                setError(fields.phone, ok ? "" : "Enter a valid phone number (digits only).");
                return ok;
            }

            function validatePassword() {
                const v = fields.password.el.value;
                const ok = v.length >= 8;
                setError(fields.password, ok ? "" : "Password must be at least 8 characters.");
                validateConfirm();
                return ok;
            }

            function validateConfirm() {
                const ok = fields.confirm_password.el.value === fields.password.el.value && fields.confirm_password.el.value.length > 0;
                setError(fields.confirm_password, ok ? "" : "Passwords do not match.");
                return ok;
            }

            fields.username.el.addEventListener("input", validateUsername);
            fields.age.el.addEventListener("input", validateAge);
            genderButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    genderButtons.forEach(b => b.classList.remove("selected"));
                    btn.classList.add("selected");
                    genderInput.value = btn.dataset.value;
                    validateGender();
                });
            });
            fields.email.el.addEventListener("input", validateEmail);
            fields.phone.el.addEventListener("input", validatePhone);
            fields.password.el.addEventListener("input", validatePassword);
            fields.confirm_password.el.addEventListener("input", validateConfirm);

            // Show/hide password toggles
            document.querySelectorAll(".toggle-password").forEach(function(btn) {
                btn.addEventListener("click", function() {
                    const targetInput = document.getElementById(btn.dataset.target);
                    const showing = targetInput.type === "text";
                    targetInput.type = showing ? "password" : "text";
                    btn.classList.toggle("showing", !showing);
                    btn.setAttribute("aria-pressed", String(!showing));
                    btn.setAttribute("aria-label", showing ? "Show password" : "Hide password");
                });
            });

            form.addEventListener("submit", function(e) {
                const checks = [
                    validateUsername(),
                    validateAge(),
                    validateGender(),
                    validateEmail(),
                    validatePhone(),
                    validatePassword(),
                    validateConfirm()
                ];
                if (checks.includes(false)) {
                    e.preventDefault();
                } else {
                    submitBtn.disabled = true;
                    submitBtn.value = "Registering...";
                }
            });
        })();
    </script>
</body>

</html>
<?php
mysqli_close($conn);
?>
