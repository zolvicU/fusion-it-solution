<?php // includes/footer.php ?>

<footer>
    <div class="footer-container">
        <p>© <?= date('Y') ?> Fusion I.T. Solutions. All rights reserved.</p>
        <p style="margin-top:8px; font-size:0.9rem; color:#666;">
            Building Tomorrow's Tech Today
        </p>
    </div>
</footer>

<!-- Main JS (hamburger menu + scroll spy + any future scripts) -->
<script src="assets/js/main.js?v=<?= time() ?>"></script>

<!-- Initialize Rellax only on desktop -->
<script>
    if (window.innerWidth > 768) {
        var rellax = new Rellax('.rellax', {
            center: true
        });
    }
</script>

</body>
</html>