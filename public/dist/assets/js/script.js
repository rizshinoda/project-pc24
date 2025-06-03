const container = document.getElementById("container");
const registerBtn = document.getElementById("register");
const loginBtn = document.getElementById("login");

registerBtn.addEventListener("click", () => {
    container.classList.add("active");
});

loginBtn.addEventListener("click", () => {
    container.classList.remove("active");
});
// Cek error register saat halaman load
window.addEventListener("DOMContentLoaded", () => {
    const hasRegisterError = container.getAttribute("data-register-error");
    if (hasRegisterError === "true") {
        container.classList.add("active");
    }
});
