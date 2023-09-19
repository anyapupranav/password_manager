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
        <a class="navbar-brand" href="index.html"><i style="font-size:24px" class="fa">&#xf023;</i> Password Manager</a>
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
            <!-- Password Type Selection -->
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="generatePasswordCheckbox" name="generatePasswordType" checked>
                <label class="form-check-label" for="generatePasswordCheckbox">Generate Password</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="generatePassphraseCheckbox" name="generatePasswordType">
                <label class="form-check-label" for="generatePassphraseCheckbox">Generate Passphrase</label>
            </div>

            <!-- Password Parameters Section -->
            <div id="passwordParameters">
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
                    <input type="number" class="form-control mt-2" id="lengthText" name="lengthText" value="16">
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="uppercase" name="uppercase">
                    <label class="form-check-label" for="uppercase">Include Uppercase Letters</label>

                    <input type="checkbox" class="form-check-input" id="lowercase" name="lowercase" style="margin-left: 10px;" checked>
                    <label class="form-check-label" for="lowercase" style="margin-left: 30px;" >Include Lowercase Letters</label>
                
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="numbers" name="numbers" checked>
                    <label class="form-check-label" for="numbers">Include Numbers</label>

                    <input type="checkbox" class="form-check-input" id="special" name="special" style="margin-left: 10px;">
                    <label class="form-check-label" for="special" style="margin-left: 30px;">Include Special Characters</label>
                </div>
            </div>

            <!-- Passphrase Parameters Section -->
            <div id="passphraseParameters" style="display: none;">
                <div class="form-group">
                    <label for="words">Number of Words:</label>
                    <input type="number" class="form-control" id="words" name="words" min="1" max="100" value="5">
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="includeNumbers" name="includeNumbers">
                    <label class="form-check-label" for="includeNumbers">Include Numbers</label>

                    <input type="checkbox" class="form-check-input" id="capitalize" name="capitalize" style="margin-left: 10px;">
                    <label class="form-check-label" for="capitalize"  style="margin-left: 30px;">Capitalize Words</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="separator" name="separator" checked>
                    <label class="form-check-label" for="separator">Include Separator</label>
                </div>
            </div>

            <div class="form-group mt-3">
                <label for="password">Generated Password/Passphrase:</label>
                <input type="text" class="form-control" id="password" name="password" style="text-align: center;" readonly>
            </div>
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-primary mt-3" id="generateButton">Generate </button>
                <button type="button" class="btn btn-secondary mt-3" id="copyToClipboardButton" style="margin-left: 30px;" data-clipboard-target="#password" disabled>Copy to Clipboard</button>
            </div>

            <div id="message" class="text-danger"></div>
            
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Clipboard.js library for copying to clipboard -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
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
        const generatePasswordCheckbox = document.getElementById("generatePasswordCheckbox");
        const generatePassphraseCheckbox = document.getElementById("generatePassphraseCheckbox");

        generatePasswordCheckbox.addEventListener("change", function () {
            if (generatePasswordCheckbox.checked) {
                // Uncheck the "Generate Passphrase" checkbox
                generatePassphraseCheckbox.checked = false;
            }
        });

        generatePassphraseCheckbox.addEventListener("change", function () {
            if (generatePassphraseCheckbox.checked) {
                // Uncheck the "Generate Password" checkbox
                generatePasswordCheckbox.checked = false;
            }
        });
    });

        document.addEventListener("DOMContentLoaded", function () {
            const lengthInput = document.getElementById("length");
            const lengthTextInput = document.getElementById("lengthText");
            const incrementButton = document.getElementById("incrementLength");
            const decrementButton = document.getElementById("decrementLength");
            const generateButton = document.getElementById("generateButton");
            const copyToClipboardButton = document.getElementById("copyToClipboardButton"); // Get the copy button here
            const passwordInput = document.getElementById("password");
            const message = document.getElementById("message");

            // Function to toggle the visibility of password/passphrase parameters
            function toggleParameters(isPassphrase) {
                const passwordParameters = document.getElementById("passwordParameters");
                const passphraseParameters = document.getElementById("passphraseParameters");

                if (isPassphrase) {
                    passwordParameters.style.display = "none";
                    passphraseParameters.style.display = "block";
                } else {
                    passwordParameters.style.display = "block";
                    passphraseParameters.style.display = "none";
                }
            }

            // Initial toggle based on the default checkbox state
            toggleParameters(false); // Password is initially selected

            // Event listeners for the checkbox toggles
            const generatePasswordCheckbox = document.getElementById("generatePasswordCheckbox");
            const generatePassphraseCheckbox = document.getElementById("generatePassphraseCheckbox");

            generatePasswordCheckbox.addEventListener("change", function () {
                toggleParameters(!generatePasswordCheckbox.checked);

                // Uncheck the "Generate Passphrase" checkbox
                generatePassphraseCheckbox.checked = false;
            });

            generatePassphraseCheckbox.addEventListener("change", function () {
                toggleParameters(generatePassphraseCheckbox.checked);
            });

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
                if (!isNaN(value) && value >= 6 && value <= 255) {
                    lengthInput.value = value;
                } else {
                    alert("Please enter a valid length between 6 and 255.");
                    lengthTextInput.value = lengthInput.value;
                }
            });

            generateButton.addEventListener("click", function () {
                const length = lengthInput.value;
                const includeUppercase = document.getElementById("uppercase").checked;
                const includeLowercase = document.getElementById("lowercase").checked;
                const includeNumbers = document.getElementById("numbers").checked;
                const includeSpecial = document.getElementById("special").checked;
                const generatePasswordType = generatePasswordCheckbox.checked;

                let generatedValue = "";

                if (generatePasswordType) {
                    // Generate a password
                    generatedValue = generatePassword(length, includeUppercase, includeLowercase, includeNumbers, includeSpecial);
                } else {
                    // Generate a passphrase
                    const numberOfWords = document.getElementById("words").value;
                    const includeSeparator = document.getElementById("separator").checked;
                    const includePassphraseNumbers = document.getElementById("includeNumbers").checked;
                    const capitalize = document.getElementById("capitalize").checked;
                    generatedValue = generatePassphrase(numberOfWords, includeSeparator, includePassphraseNumbers, capitalize);
                }

                passwordInput.value = generatedValue;

                // Enable the copy to clipboard button only if a value is generated
                if (generatedValue) {
                    copyToClipboardButton.removeAttribute("disabled");
                } else {
                    copyToClipboardButton.setAttribute("disabled", "true");
                }
            });

            const clipboard = new ClipboardJS(copyToClipboardButton);
            clipboard.on("success", function (e) {
                message.innerHTML = "Value copied to clipboard!";
                e.clearSelection();
            });

            clipboard.on("error", function (e) {
                message.innerHTML = "Copy to clipboard failed. Please select and copy manually.";
            });

            function generatePassword(length, includeUppercase, includeLowercase, includeNumbers, includeSpecial) {
                // Validation: Ensure at least one character type is selected
                if (!includeUppercase && !includeLowercase && !includeNumbers && !includeSpecial) {
                    message.innerHTML = "Please select at least one character type.";
                    return "";
                }

                const charset = [];
                if (includeUppercase) charset.push("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
                if (includeLowercase) charset.push("abcdefghijklmnopqrstuvwxyz");
                if (includeNumbers) charset.push("0123456789");
                if (includeSpecial) charset.push("!@#$%?-_+=^&*");

                if (charset.length === 0) {
                    message.innerHTML = "Please select at least one character type.";
                    return "";
                }

                const charsetString = charset.join("");
                let password = "";
                for (let i = 0; i < length; i++) {
                    const randomIndex = Math.floor(Math.random() * charsetString.length);
                    password += charsetString.charAt(randomIndex);
                }
                message.innerHTML = ""; // Clear any previous error message
                return password;
            }

            function generatePassphrase(numberOfWords, includeSeparator, includeNumbers, capitalize) {
                // Define your list of words here
                const wordList = [
                                    "apple", "banana", "cherry", "date", "elderberry",
                                    "fig", "grape", "honeydew", "kiwi", "lemon",
                                    "mango", "orange", "papaya", "peach", "pear",
                                    "plum", "strawberry", "watermelon", "apricot", "blueberry",
                                    "blackberry", "raspberry", "cranberry", "coconut", "pineapple",
                                    "guava", "passionfruit", "lime", "grapefruit", "tangerine",
                                    "cantaloupe", "pomegranate", "kiwifruit", "mulberry", "lychee",
                                    "dragonfruit", "persimmon", "boysenberry", "currant", "nectarine",
                                    "starfruit", "avocado", "jackfruit", "mangosteen", "gooseberry",
                                    "plantain", "pumpkin", "squash", "zucchini", "carrot",
                                    "broccoli", "cauliflower", "cabbage", "kale", "spinach",
                                    "lettuce", "tomato", "cucumber", "bell pepper", "onion",
                                    "garlic", "potato", "sweet potato", "asparagus", "celery",
                                    "corn", "peas", "beans", "eggplant", "mushroom",
                                    "beetroot", "radish", "turnip", "rutabaga", "artichoke",
                                    "leek", "okra", "parsnip", "salsify", "yam",
                                    "chickpea", "lentil", "soybean", "quinoa", "barley",
                                    "oat", "rice", "wheat", "cornbread", "baguette",
                                    "croissant", "pretzel", "biscuit", "pancake", "waffle",
                                    "muffin", "scone", "donut", "cake", "pie"
                                ];

                if (numberOfWords < 1 || numberOfWords > 100) {
                    message.innerHTML = "Number of words should be between 1 and 100.";
                    return "";
                }

                let passphrase = "";
                for (let i = 0; i < numberOfWords; i++) {
                    const randomIndex = Math.floor(Math.random() * wordList.length);
                    let word = wordList[randomIndex];

                    if (capitalize) {
                        word = word.charAt(0).toUpperCase() + word.slice(1);
                    }

                    passphrase += word;

                    if (includeSeparator && i < numberOfWords - 1) {
                        passphrase += "-";
                    }
                }

                if (includeNumbers) {
                    const randomNum = Math.floor(Math.random() * 10000);
                    passphrase += randomNum;
                }

                message.innerHTML = ""; // Clear any previous error message
                return passphrase;
            }
        });
    </script>
</body>

</html>
