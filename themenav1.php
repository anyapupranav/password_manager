                <!-- Add the dark mode toggle button to the navbar -->
                <li class="nav-item">
                    <button id="darkModeToggle" class="btn btn-link text-white">
                        <i class="fas fa-moon"></i> Dark Mode
                    </button>
                </li>
            </ul>
        </div>
    </nav>

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
    </script>
</body>

</html>