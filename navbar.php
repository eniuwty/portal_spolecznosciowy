<div class="navbar">
    <div class="navbar-left">
        <h2>Instagram</h2>
    </div>
    <div class="navbar-center">
        <?php if (isset($_SESSION['username'])): ?>
            <p>Jesteś zalogowany jako: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>" atl="Avatar"class="navbar-avatar" loading="lazy">
        </p>
        <?php else: ?>
            <p>Nie jesteś zalogowany</p>
        <?php endif; ?>
    </div>
    <div class="navbar-right">
        <a href="index.php">Strona główna</a>
        <a href="profile.php">Profil</a>
        <a href="gallery.php">Galeria</a>
        <?php if (isset($_SESSION['username'])): ?>
            <a href="logout.php">Wyloguj</a>
        <?php else: ?>
            <a href="login.php">Zaloguj</a>
            <a href="register.php">Rejestracja</a>
        <?php endif; ?>
    </div>
</div>