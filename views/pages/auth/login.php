<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Barkea Resort</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Our CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="login-page">

    <main class="container">
        <div class="row justify-content-center align-items-center min-vh-100">

            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                <div class="card shadow-sm login-card">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <h1 class="h3 mb-2">Barkea Resort</h1>
                            <p class="text-muted mb-0">
                                Construction Management System
                            </p>
                        </div>

                        <h2 class="h5 mb-4 text-center">
                            Sign in to your account
                        </h2>


                        <form id="loginForm" method="post">

                            <div class="mb-3">
                                <label for="login" class="form-label">
                                    Username or Email
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="login"
                                    name="login"
                                    placeholder="Enter username or email"
                                    autocomplete="username"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Password
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>

                            <!-- Message area for future validation -->
                            <div
                                id="loginMessage"
                                class="alert alert-danger d-none"
                                role="alert"
                            ></div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Sign In
                            </button>

                        </form>

                    </div>
                </div>

                <p class="text-center text-muted small mt-3">
                    Barkea Resort Management System
                </p>

            </div>

        </div>
    </main>

    <script src="/assets/js/app.js"></script>

</body>
</html>