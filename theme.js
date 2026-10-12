document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem("theme");
    const themeCheckbox = document.getElementById("checkboxTheme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark-mode");
        if (themeCheckbox) {
            themeCheckbox.checked = true;
        }
    }
});

function toggleTheme() {
    const themeCheckbox = document.getElementById("checkboxTheme");
    
    if (themeCheckbox && themeCheckbox.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem("theme", "dark");
    } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem("theme", "light");
    }
}