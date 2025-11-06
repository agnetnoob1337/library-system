document.getElementById('showRegisterForm').addEventListener('click', function() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
    document.getElementById('showLoginForm').style.display = 'block';
    document.getElementById('showRegisterForm').style.display = 'none';
});
document.getElementById('showLoginForm').addEventListener('click', function() {
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('showRegisterForm').style.display = 'block';
    document.getElementById('showLoginForm').style.display = 'none';
});

async function checkUserExists(type, value) {
    const formData = new FormData();
    formData.append(type, value);

    const response = await fetch('./php/check-user.php', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();
    return result;
}

// Username check
document.getElementById('reg-username').addEventListener('blur', async () => {
    const username = document.getElementById('reg-username').value.trim();
    const error = document.getElementById('username-error');
    if (username.length === 0) {
        error.textContent = '';
        return;
    }

    const res = await checkUserExists('username', username);
    if (res.status === 'exists') {
        error.textContent = 'Username already exists.';
    } else {
        error.textContent = '';
    }
});

// Email check
document.getElementById('reg-email').addEventListener('blur', async () => {
    const email = document.getElementById('reg-email').value.trim();
    const error = document.getElementById('email-error');
    if (email.length === 0) {
        error.textContent = '';
        return;
    }

    const res = await checkUserExists('email', email);
    if (res.status === 'exists') {
        error.textContent = 'Email already exists.';
    } else {
        error.textContent = '';
    }
});

document.getElementById('registerForm').addEventListener('submit', (e) => {
    const usernameErr = document.getElementById('username-error').textContent;
    const emailErr = document.getElementById('email-error').textContent;

    if (usernameErr || emailErr) {
        e.preventDefault();
    }
});
