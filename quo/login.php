<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gravitti Quotation System</title>
    <meta name="description" content="Login ke sistem penawaran Gravitti Technology">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            overflow: hidden;
            position: relative;
        }

        /* Animated gradient background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg at 50% 50%,
                #0f172a 0deg,
                #1e3a5f 60deg,
                #0f172a 120deg,
                #2d1b4e 180deg,
                #0f172a 240deg,
                #1e3a5f 300deg,
                #0f172a 360deg
            );
            animation: rotateGradient 20s linear infinite;
            z-index: 0;
        }

        @keyframes rotateGradient {
            100% { transform: rotate(360deg); }
        }

        /* Floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            z-index: 1;
            animation: float 8s ease-in-out infinite;
        }
        .orb-1 {
            width: 400px; height: 400px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            top: -10%; left: -5%;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 350px; height: 350px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            bottom: -15%; right: -5%;
            animation-delay: -4s;
        }
        .orb-3 {
            width: 250px; height: 250px;
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            top: 50%; left: 60%;
            animation-delay: -2s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* Login card */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .login-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 48px 40px 40px;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.05),
                0 25px 50px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255,255,255,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-2px);
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.08),
                0 30px 60px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(255,255,255,0.12);
        }

        /* Logo section */
        .logo-section {
            text-align: center;
            margin-bottom: 36px;
        }

        .logo-section img {
            height: 44px;
            margin-bottom: 12px;
            filter: drop-shadow(0 4px 12px rgba(245, 158, 11, 0.3));
        }

        .logo-title {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .logo-subtitle {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
            font-weight: 400;
        }

        /* Alert */
        .alert-login {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-login i {
            font-size: 16px;
            color: #ef4444;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 18px;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(71, 85, 105, 0.4);
            border-radius: 14px;
            color: #f1f5f9;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: #475569;
        }

        .form-input:focus {
            border-color: #f59e0b;
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15), 0 0 20px rgba(245, 158, 11, 0.08);
        }

        .form-input:focus + .input-icon-bg i.input-icon,
        .form-input:focus ~ i.input-icon {
            color: #f59e0b;
        }

        .input-wrapper:focus-within i.input-icon {
            color: #f59e0b;
        }

        /* Password toggle */
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #475569;
            cursor: pointer;
            padding: 4px;
            font-size: 18px;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .toggle-password:hover { color: #94a3b8; }

        /* Login button */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            border-radius: 14px;
            color: #0f172a;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.02em;
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer link */
        .login-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .login-footer p {
            color: #64748b;
            font-size: 13px;
        }

        .login-footer a {
            color: #f59e0b;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #fbbf24;
            text-decoration: underline;
        }

        /* Decorative grid */
        .grid-pattern {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 2;
            pointer-events: none;
        }

        /* Copyright */
        .copyright {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #334155;
            font-size: 11px;
            z-index: 10;
            white-space: nowrap;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 36px 24px 32px;
                border-radius: 20px;
            }
            .login-wrapper {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Decorative elements -->
    <div class="grid-pattern"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo-section">
                <img src="assets/img/logo.png" alt="Gravitti Technology Logo">
                <div class="logo-title">Quotation</div>
                <div class="logo-subtitle">Sistem Penawaran Gravitti Technology</div>
            </div>

            <!-- Error Alert -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert-login">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="auth_logic.php" method="POST" autocomplete="on">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-fill input-icon"></i>
                        <input type="text" class="form-input" id="username" name="username" placeholder="Masukkan username" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-input" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="bi bi-eye-slash-fill"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>&nbsp; Masuk
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>Belum punya akun? <a href="signup.php">Daftar di sini</a></p>
            </div>
        </div>
    </div>

    <div class="copyright">© <?php echo date('Y'); ?> Gravitti Technology. All rights reserved.</div>

    <script>
        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye-slash-fill');
            icon.classList.toggle('bi-eye-fill');
        });

        // Add subtle entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.login-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>