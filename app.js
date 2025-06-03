document.getElementById('show-signup').addEventListener('click', function() {
    document.getElementById('signin-box').classList.add('hidden');
    document.getElementById('signup-box').classList.remove('hidden');
});

document.getElementById('show-signin').addEventListener('click', function() {
    document.getElementById('signup-box').classList.add('hidden');
    document.getElementById('signin-box').classList.remove('hidden');
});