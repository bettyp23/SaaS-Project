<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo Tracker - Organize Your Life</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }
        .nav-buttons {
            display: flex;
            gap: 16px;
        }
        .btn-login {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 24px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .btn-signup {
            background: white;
            color: #667eea;
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
        }
        .hero-content {
            max-width: 800px;
        }
        .hero h1 {
            font-size: 56px;
            font-weight: 800;
            color: white;
            margin-bottom: 24px;
            line-height: 1.2;
        }
        .hero p {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }
        .btn-primary {
            background: white;
            color: #667eea;
            padding: 16px 40px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 16px 40px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .features {
            background: white;
            padding: 80px 40px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            text-align: center;
        }
        .feature-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .feature-card h3 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
        }
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }
            .hero p {
                font-size: 16px;
            }
            .cta-buttons {
                flex-direction: column;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">Todo Tracker</div>
        <nav class="nav-buttons">
            <a href="{{ route('login') }}" class="btn-login">Log In</a>
            <a href="{{ route('register') }}" class="btn-signup">Sign Up</a>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Organize Your Life, One Task at a Time</h1>
            <p>Powerful task management for teams and individuals. Collaborate, stay organized, and get things done.</p>
            
            <div class="cta-buttons">
                <a href="{{ route('register') }}" class="btn-primary">Get Started Free</a>
                <a href="{{ route('login') }}" class="btn-secondary">Log In</a>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3>Task Management</h3>
                <p>Create, organize, and track your todos with ease. Set priorities, due dates, and reminders.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3>Team Collaboration</h3>
                <p>Work together with your team. Share tasks, assign responsibilities, and collaborate seamlessly.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏷️</div>
                <h3>Smart Tags</h3>
                <p>Organize your tasks with tags. Filter and search through your todos effortlessly.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Analytics</h3>
                <p>Track your productivity with detailed statistics and insights into your work patterns.</p>
            </div>
        </div>
    </section>
</body>
</html>
