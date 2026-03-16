function openLoginModal() {
    document.getElementById("loginModal").style.display = "flex";
    setTimeout(
        () =>
            (document.querySelector(".login-form-container").style.transform =
                "scale(1)"),
        100,
    );
}

function closeLoginModal() {
    document.getElementById("loginModal").style.display = "none";
}

function openRegisterModal() {
    document.getElementById("registerModal").style.display = "flex";
}

function closeRegisterModal() {
    document.getElementById("registerModal").style.display = "none";
}

function handleLogin(e) {
    e.preventDefault();
    // Interface only - backend placeholder
    alert("Login submitted! (Backend integration pending)");
    closeLoginModal();
}

// Close modals on outside click
window.onclick = function (event) {
    const loginModal = document.getElementById("loginModal");
    const registerModal = document.getElementById("registerModal");
    if (event.target === loginModal) closeLoginModal();
    if (event.target === registerModal) closeRegisterModal();
};
