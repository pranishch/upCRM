<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Callback System</title>
    <link href="{{ asset('resources/css/style.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        /* Body Styling */
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Login Container */
        .login-container {
            display: flex;
            width: 100vw;
            height: 100vh;
            background-color: #fff;
            border-radius: 0;
            box-shadow: none;
            overflow: hidden;
        }

        /* Login Left Section */
        .login-left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, orange);
            padding: 20px;
        }

        /* Login Branding */
        .login-branding {
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .login-image {
            width: 100%;
            height: auto;
        }

        /* Login Right Section */
        .login-right {
            flex: 1;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Login Form */
        .login-form {
            width: 100%;
            max-width: 400px;
        }

        .login-form h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: orange;
            box-shadow: orange;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: orange;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: orange;
        }

        .error-messages {
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            text-align: center;
        }

        .field-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .error-field {
            border-color: #dc3545 !important;
        }

        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        @media (max-width: 700px) {
            .login-container {
                flex-direction: column;
                width: 100vw;
                height: 100vh;
            }

            .login-left {
                padding: 8px;
                width: 100%;
                min-height: 25vh;
                /* margin-bottom: 169px; */
            }

            .login-right {
                padding: 12px;
            }

            .login-branding {
                max-width: 200px;
                width: 100%;
            }

            .login-image {
                max-width: 300px;
                width: 100%;
            }

            .login-form {
                max-width: 280px;
                /* margin-top: -331px; */
            }

            .login-form h2 {
                font-size: 18px;
            }

            .form-group input {
                padding: 8px 10px;
                font-size: 12px;
            }

            .btn {
                padding: 8px;
                font-size: 12px;
            }

            .password-toggle {
                right: 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="login-branding">
                <img src="{{ asset('images/uptech.png') }}" class="login-image" alt="Uptech Logo">
            </div>
        </div>
        <div class="login-right">
            <form class="login-form" id="loginForm" method="POST" action="{{ route('login.post') }}">
                @csrf
                <h2>Login to your account</h2>

                @if ($errors->has('error'))
                    <div class="error-messages">
                        <div class="error-message">{{ $errors->first('error') }}</div>
                    </div>
                @endif

                <div class="form-group">
                    <label for="identifier">Email or Username</label>
                    <input
                        type="text"
                        id="identifier"
                        name="email"
                        class="@error('email') error-field @enderror"
                        placeholder="Enter email or username"
                        required
                        autocomplete="username"
                        value="{{ old('email') }}"
                    >
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" class="@error('password') error-field @enderror" placeholder="********" required autocomplete="current-password">
                        <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                    </div>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn primary-btn">Login</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const identifier = document.getElementById('identifier').value.trim();
            const password = document.getElementById('password').value;
            let hasError = false;

            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

            if (!identifier) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.textContent = 'Email or username is required.';
                document.getElementById('identifier').parentNode.appendChild(errorDiv);
                hasError = true;
            }

            if (!password) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.textContent = 'Password is required.';
                document.getElementById('password').parentNode.appendChild(errorDiv);
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                this.classList.add('shake');
                setTimeout(() => this.classList.remove('shake'), 500);
            }
        });
    </script>
</body>
</html>