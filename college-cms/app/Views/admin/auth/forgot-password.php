<?php
/** @var string $title */
/** @var string|null $error */
/** @var string|null $success */
/** @var string|null $reset_link */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Forgot Password') ?> | College CMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            font-family: "Source Sans 3", system-ui, sans-serif;
            background: linear-gradient(160deg, #0f2744 0%, #132f3f 50%, #163a2e 100%);
            display: grid; place-items: center; padding: 1.5rem;
        }
        .auth-card {
            width: min(420px, 100%);
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 18px 50px rgba(0,0,0,.28);
            padding: 1.75rem;
        }
    </style>
</head>
<body>
<div class="auth-card">
    <h1 class="h4 mb-1">Forgot Password</h1>
    <p class="text-muted">Enter your account email to generate a reset link.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><?= e((string) $error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success py-2"><?= e((string) $success) ?></div>
    <?php endif; ?>
    <?php if (!empty($reset_link)): ?>
        <div class="alert alert-info small">
            <strong>Debug reset link:</strong><br>
            <a href="<?= e((string) $reset_link) ?>"><?= e((string) $reset_link) ?></a>
        </div>
    <?php endif; ?>

    <form action="<?= e(url('forgot-password')) ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= old('email') ?>" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>
    </form>
    <p class="mb-0 text-center"><a href="<?= e(url('login')) ?>">Back to login</a></p>
</div>
</body>
</html>
