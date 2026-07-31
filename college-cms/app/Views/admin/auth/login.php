<?php
/** @var string $title */
/** @var string|null $error */
/** @var string|null $success */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Login') ?> | College CMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --cms-ink: #0f2744;
            --cms-accent: #1f6f5b;
        }
        body {
            min-height: 100vh;
            font-family: "Source Sans 3", system-ui, sans-serif;
            background:
                radial-gradient(circle at 15% 20%, rgba(31, 111, 91, 0.35), transparent 45%),
                radial-gradient(circle at 85% 10%, rgba(56, 120, 180, 0.35), transparent 40%),
                linear-gradient(160deg, #0f2744 0%, #132f3f 50%, #163a2e 100%);
        }
        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
        }
        .auth-card {
            width: min(420px, 100%);
            background: rgba(255, 255, 255, 0.96);
            border-radius: 1rem;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }
        .auth-brand {
            background: linear-gradient(120deg, var(--cms-ink), var(--cms-accent));
            color: #fff;
            padding: 1.5rem 1.75rem 1.25rem;
        }
        .auth-brand h1 {
            font-size: 1.6rem;
            margin: 0;
            letter-spacing: 0.02em;
        }
        .auth-body { padding: 1.5rem 1.75rem 1.75rem; }
        .hint { color: rgba(255,255,255,0.75); font-size: 0.85rem; margin-top: 0.75rem; text-align: center; }
    </style>
</head>
<body>
<div class="auth-shell">
    <div>
        <div class="auth-card">
            <div class="auth-brand">
                <h1><strong>College</strong> CMS</h1>
                <p class="mb-0 opacity-75">Admin Sign In</p>
            </div>
            <div class="auth-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2"><?= e((string) $error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2"><?= e((string) $success) ?></div>
                <?php endif; ?>

                <form action="<?= e(url('login')) ?>" method="post" autocomplete="off">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= old('email') ?>" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                            <label class="form-check-label" for="remember">Remember Me</label>
                        </div>
                        <a href="<?= e(url('forgot-password')) ?>">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </form>
            </div>
        </div>
        <p class="hint">Default: admin@college.local / Admin@123</p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
