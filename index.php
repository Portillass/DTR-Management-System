<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jaculbe's Lechon - Staff Attendance System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            overflow-x: hidden;
        }

        /* Enhanced Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            transition: all 0.3s ease;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--secondary-color) !important;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            background: linear-gradient(135deg, #f6f9fc 0%, #eef2f7 100%);
            padding-top: 80px;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .hero-buttons .btn {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            margin-right: 1rem;
            margin-bottom: 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-primary:hover, .btn-outline-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Animated Background */
        .animated-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .animated-bg span {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background: rgba(52, 152, 219, 0.1);
            animation: move 25s linear infinite;
            bottom: -150px;
        }

        @keyframes move {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: white;
        }

        .feature-card {
            padding: 2.5rem;
            border-radius: 20px;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 0;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-card:hover::before {
            opacity: 0.05;
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .feature-text {
            color: #666;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        /* About Section */
        .about-section {
            padding: 100px 0;
            background: var(--light-bg);
            position: relative;
            overflow: hidden;
        }

        .about-image {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transform: perspective(1000px) rotateY(-5deg);
            transition: all 0.3s ease;
        }

        .about-image:hover {
            transform: perspective(1000px) rotateY(0deg);
        }

        .about-content {
            padding: 2rem;
        }

        .about-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .about-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #666;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils"></i> Jaculbe's Lechon
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about"><i class="fas fa-info-circle"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#signupModal">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="animated-bg">
            <span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span><span></span>
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content" data-aos="fade-right">
                    <h1 class="hero-title">Jaculbe's Lechon Staff Management</h1>
                    <p class="hero-subtitle">Efficiently manage and track your staff attendance with our comprehensive system designed specifically for Jaculbe's Lechon.</p>
                    <div class="hero-buttons">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt"></i> Staff Login
                        </button>
                        <a href="#about" class="btn btn-outline-primary">
                            <i class="fas fa-info-circle"></i> Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="download.jpeg" alt="Jaculbe's Lechon Staff System" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12" data-aos="fade-up">
                    <h2 class="display-4">Staff Management Features</h2>
                    <p class="lead text-muted">Streamline your restaurant's staff attendance tracking</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <i class="fas fa-clock feature-icon"></i>
                        <h3 class="feature-title">Time Tracking</h3>
                        <p class="feature-text">Monitor staff clock-in and clock-out times with precision for accurate payroll processing.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <i class="fas fa-chart-bar feature-icon"></i>
                        <h3 class="feature-title">Attendance Reports</h3>
                        <p class="feature-text">Generate detailed reports for staff attendance, overtime, and leave management.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <i class="fas fa-mobile-alt feature-icon"></i>
                        <h3 class="feature-title">Mobile Access</h3>
                        <p class="feature-text">Staff can easily check-in and view their schedules from any device.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4" data-aos="fade-right">
                    <img src="image.png" alt="Jaculbe's Lechon" class="img-fluid about-image">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="about-content">
                        <h2 class="about-title">About Our System</h2>
                        <p class="about-text">
                            Jaculbe's Lechon Staff Attendance Management System is specifically designed to streamline 
                            the process of tracking and managing staff attendance in our restaurant. This comprehensive 
                            solution helps ensure efficient workforce management while maintaining accurate records.
                        </p>
                        <p class="about-text">
                            Key benefits for our staff:
                        </p>
                        <ul class="about-text">
                            <li>Easy clock-in/clock-out process</li>
                            <li>Real-time attendance tracking</li>
                            <li>Access to personal attendance history</li>
                            <li>Automated overtime calculation</li>
                            <li>Leave management system</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel"><i class="fas fa-sign-in-alt"></i> Staff Login - Jaculbe's Lechon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loginAlert" class="alert d-none"></div>
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="loginEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-login">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal">Sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signupModalLabel"><i class="fas fa-user-plus"></i> Sign Up - Jaculbe's Lechon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="signupAlert" class="alert d-none"></div>
                    <form id="signupForm">
                        <div class="mb-3">
                            <label for="signupName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="signupName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="signupEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="signupEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="signupPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="signupPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-login">Sign Up</button>
                    </form>
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Login Form Handler
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginAlert = document.getElementById('loginAlert');
            
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(loginForm);
                
                fetch('process_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loginAlert.classList.remove('d-none', 'alert-success', 'alert-danger');
                    if (data.status === 'success') {
                        loginAlert.classList.add('alert-success');
                        loginAlert.textContent = data.message;
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        loginAlert.classList.add('alert-danger');
                        loginAlert.textContent = data.message;
                    }
                })
                .catch(error => {
                    loginAlert.classList.remove('d-none');
                    loginAlert.classList.add('alert-danger');
                    loginAlert.textContent = 'An error occurred. Please try again.';
                });
            });

            // Signup Form Handler
            const signupForm = document.getElementById('signupForm');
            const signupAlert = document.getElementById('signupAlert');
            
            signupForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(signupForm);
                
                fetch('process_signup.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    signupAlert.classList.remove('d-none', 'alert-success', 'alert-danger');
                    if (data.status === 'success') {
                        signupAlert.classList.add('alert-success');
                        signupAlert.textContent = data.message;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        signupAlert.classList.add('alert-danger');
                        signupAlert.textContent = data.message;
                    }
                })
                .catch(error => {
                    signupAlert.classList.remove('d-none');
                    signupAlert.classList.add('alert-danger');
                    signupAlert.textContent = 'An error occurred. Please try again.';
                });
            });
        });
    </script>
</body>
</html> 