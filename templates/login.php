<h1>Log In</h1>
<form method="post">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= $error ?>
        </div>
    <?php endif; ?>
    <?php if (isset($wasLoggedOut)): ?>
        <div class="alert alert-success" role="alert">
            You have been successfully logged out.
        </div>
    <?php endif; ?>
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" name="username" id="username">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" name="password" id="password">
    </div>
    <button type="submit" class="btn btn-primary">Log In</button>
</form>
