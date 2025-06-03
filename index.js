document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelector('.sidebar a.active')?.classList.remove('active');
        this.classList.add('active');
    });
});
