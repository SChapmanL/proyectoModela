document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('error-message');

    if (username === 'Directora' && password === 'pass') {
        sessionStorage.setItem('userRole', 'Directora');
        window.location.href = 'index.php';
    } else if (username === 'Profesora1' && password === 'pass') {
        sessionStorage.setItem('userRole', 'Profesora1');
        window.location.href = 'index.php';
    } else {
        errorMessage.textContent = 'Usuario o contraseña incorrectos.';
    }
});
