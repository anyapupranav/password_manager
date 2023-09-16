<?php
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Generator</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome CSS for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>

<style>
    .login-container {
        max-width: 80%;
        margin: 0 auto;
        padding: 10px;
        background-color: rgb(232, 242, 242);
        border-radius: 5px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar-nav {
        margin-left: auto;
    }

    /* Dark mode styles */
    body.dark-mode {
        background-color: #1F1B24; /* Dark background color */
        color: #fff; /* White text color */
    }
</style>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <a class="navbar-brand" href="index.html">Password Manager</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php
                  // Check if the user is logged in
                  session_start();
                  if (isset($_SESSION['passed_user_email'])) {

                    // User is logged in, display "My Account" and "Logout"
                    echo '
                    <li class="nav-item">
                    <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link" href="shared_passwords.php">Shared Passwords</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link active" href="password_generator.php">Password Generator</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="myaccount.php">My Account</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                    ';
                  }
                ?>

                <!-- Add the dark mode toggle button to the navbar -->
                <li class="nav-item">
                    <button id="darkModeToggle" class="btn btn-link text-white">
                        <i class="fas fa-moon"></i> Dark Mode
                    </button>
                </li>
            </ul>
        </div>
    </nav>

        <h1 style="text-align: center;">Password Generator</h1>

    <main class="container mt-4">

        <form action="" method="POST" id="passwordForm">
            <div class="form-group">
                <label for="length">Password Length:</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-outline-secondary" id="decrementLength">-</button>
                    </div>
                    <input type="range" class="form-control" id="length" name="length" min="6" max="255" value="16">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" id="incrementLength">+</button>
                    </div>
                </div>
                <input type="text" class="form-control mt-2" id="lengthText" name="lengthText" value="16">
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="uppercase" name="uppercase">
                <label class="form-check-label" for="uppercase">Include Uppercase Letters</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="lowercase" name="lowercase" checked>
                <label class="form-check-label" for="lowercase">Include Lowercase Letters</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="numbers" name="numbers" checked>
                <label class="form-check-label" for="numbers">Include Numbers</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="special" name="special">
                <label class="form-check-label" for="special">Include Special Characters</label>
            </div>
            <button type="button" class="btn btn-primary mt-3" id="generateButton">Generate Password</button>
            <div class="form-group mt-3">
                <label for="password">Generated Password:</label>
                <input type="text" class="form-control" id="password" name="password" style="text-align: center;"
                    readonly>
            </div>
        </form>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const darkModeToggle = document.getElementById("darkModeToggle");
            const body = document.body;

            // Check the user's preference for dark mode in local storage
            const isDarkModePreferred = localStorage.getItem("darkMode") === "true";

            // Apply dark mode class if preferred
            if (isDarkModePreferred) {
                body.classList.add("dark-mode");
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
            }

            // Toggle dark mode on button click
            darkModeToggle.addEventListener("click", function () {
                if (body.classList.contains("dark-mode")) {
                    // Switch to light mode
                    body.classList.remove("dark-mode");
                    localStorage.setItem("darkMode", "false");
                    darkModeToggle.innerHTML = '<i class="fas fa-moon"></i> Dark Mode';
                } else {
                    // Switch to dark mode
                    body.classList.add("dark-mode");
                    localStorage.setItem("darkMode", "true");
                    darkModeToggle.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
                }
            });
        });
            document.addEventListener("DOMContentLoaded", function () {
            const lengthInput = document.getElementById("length");
            const lengthTextInput = document.getElementById("lengthText");
            const incrementButton = document.getElementById("incrementLength");
            const decrementButton = document.getElementById("decrementLength");

            // Update the text input when the slider is moved
            lengthInput.addEventListener("input", function () {
                lengthTextInput.value = lengthInput.value;
            });

            incrementButton.addEventListener("click", function () {
                lengthInput.stepUp();
                lengthTextInput.value = lengthInput.value;
            });

            decrementButton.addEventListener("click", function () {
                lengthInput.stepDown();
                lengthTextInput.value = lengthInput.value;
            });

            lengthTextInput.addEventListener("change", function () {
                const value = parseInt(lengthTextInput.value);
                if (!isNaN(value) && value >= 6 && value <= 1024) {
                    lengthInput.value = value;
                } else {
                    alert("Please enter a valid length between 6 and 1024.");
                    lengthTextInput.value = lengthInput.value;
                }
            });

            const generateButton = document.getElementById("generateButton");
            const passwordInput = document.getElementById("password");

            generateButton.addEventListener("click", function () {
                const length = lengthInput.value;
                const includeUppercase = document.getElementById("uppercase").checked;
                const includeLowercase = document.getElementById("lowercase").checked;
                const includeNumbers = document.getElementById("numbers").checked;
                const includeSpecial = document.getElementById("special").checked;

                const password = generatePassword(length, includeUppercase, includeLowercase, includeNumbers, includeSpecial);
                passwordInput.value = password;
            });

            function generatePassword(length, includeUppercase, includeLowercase, includeNumbers, includeSpecial) {
                const charset = [];
                if (includeUppercase) charset.push("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
                if (includeLowercase) charset.push("abcdefghijklmnopqrstuvwxyz");
                if (includeNumbers) charset.push("0123456789");
                if (includeSpecial) charset.push("!@#$%^&*()_+{}[]|:;<>,.?~");

                if (charset.length === 0) {
                    alert("Please select at least one character type.");
                    return "";
                }

                const charsetString = charset.join("");
                let password = "";
                for (let i = 0; i < length; i++) {
                    const randomIndex = Math.floor(Math.random() * charsetString.length);
                    password += charsetString.charAt(randomIndex);
                }
                return password;
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
