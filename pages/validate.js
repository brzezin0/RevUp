document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form');
    var usernameInput = document.getElementById('username');
    var fullnameInput = document.getElementById('fullname');
    var emailInput = document.getElementById('email');
    var phoneInput = document.getElementById('phone');
    var passwordInput = document.getElementById('password');

    form.addEventListener('submit', function (event) {
        var usernameValue = usernameInput.value.trim();
        var fullnameValue = fullnameInput.value.trim();
        var emailValue = emailInput.value.trim();
        var phoneValue = phoneInput.value.trim();
        var passwordValue = passwordInput.value.trim();
        
        if (usernameValue.length <= 6) {
            alert('Username must be longer than 6 characters');
            event.preventDefault(); 
        }

        if (!/^[a-zA-Z]+$/.test(fullnameValue)) {
            alert('Full name can only contain characters');
            event.preventDefault(); 
        }
        
        if (/^\d+$/.test(emailValue)) {
            alert('Email cannot consist of only digits');
            event.preventDefault();
        }
        
        if (!emailValue.includes('@')) {
            alert('Wrong email format');
            event.preventDefault(); 
        }

        if (phoneValue.length < 9 || phoneValue.length > 12 || !/^[0-9]+$/.test(phoneValue)){
            alert('Invalid phone number format');
            event.preventDefault();
        }
        
        if (passwordValue.length < 8){
            alert('Password must be at least 8 characters long');
            event.preventDefault();
        }

        if (!/^\d+$/.test(nipValue)) {
            alert('NIP can only consist of digits');
            event.preventDefault();
        }
    });
});
