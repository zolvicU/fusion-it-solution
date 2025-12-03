<footer>
    <p>© 2025 Fusion I.T. Solution. All rights reserved.</p>
</footer>

<script src="assets/js/rellax.min.js"></script>
<script>
    // Start parallax
    var rellax = new Rellax('.rellax', { center: true });

    // Mobile menu toggle
    document.querySelector('.hamburger').addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
    });
</script>
</body>
</html>