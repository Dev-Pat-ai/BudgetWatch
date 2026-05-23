<?php require 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetWatch — Master Your Finances</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background: #F0F2F5; color: #0F172A; }

        .nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; justify-content: space-between; align-items: center;
            padding: 16px 60px;
            background: rgba(15,23,42,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; }
        .nav-brand-ico { width: 32px; height: 32px; background: #10B981; border-radius: 7px; display: flex; align-items: center; justify-content: center; }
        .nav-brand-name { font-size: 1.1rem; font-weight: 700; color: #fff; }
        .nav-links { display: flex; align-items: center; gap: 12px; }
        .nav-link { color: #94A3B8; text-decoration: none; font-size: 0.875rem; font-weight: 500; padding: 8px 14px; border-radius: 7px; transition: 0.2s; }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.06); }
        .nav-btn { padding: 9px 20px; background: #10B981; color: #fff; border-radius: 8px; font-size: 0.875rem; font-weight: 700; text-decoration: none; transition: 0.2s; }
        .nav-btn:hover { background: #059669; }

        .hero {
            min-height: 100vh;
            background: #0F172A;
            display: flex; align-items: center; justify-content: center;
            text-align: center;
            padding: 120px 20px 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; top: -200px; left: 50%; transform: translateX(-50%);
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(16,185,129,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(16,185,129,0.12); color: #10B981;
            border: 1px solid rgba(16,185,129,0.25);
            padding: 6px 14px; border-radius: 20px;
            font-size: 0.8rem; font-weight: 600; margin-bottom: 28px;
        }
        .hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.8rem);
            font-weight: 800; color: #fff;
            line-height: 1.15; margin-bottom: 20px;
            letter-spacing: -0.02em;
        }
        .hero h1 span { color: #10B981; }
        .hero p {
            font-size: 1.1rem; color: #94A3B8;
            max-width: 520px; margin: 0 auto 40px;
            line-height: 1.7;
        }
        .hero-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .btn-hero-primary {
            padding: 15px 32px; background: #10B981; color: #fff;
            border-radius: 10px; font-size: 1rem; font-weight: 700;
            text-decoration: none; transition: 0.2s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-hero-primary:hover { background: #059669; transform: translateY(-2px); }
        .btn-hero-secondary {
            padding: 15px 32px; background: transparent;
            border: 1.5px solid rgba(255,255,255,0.2);
            color: #fff; border-radius: 10px; font-size: 1rem; font-weight: 600;
            text-decoration: none; transition: 0.2s;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.4); }

        .hero-stats {
            display: flex; gap: 48px; justify-content: center;
            margin-top: 64px; flex-wrap: wrap;
        }
        .hero-stat-num { font-size: 1.8rem; font-weight: 800; color: #fff; }
        .hero-stat-lbl { font-size: 0.8rem; color: #64748B; font-weight: 500; margin-top: 2px; }

        .features-section {
            padding: 80px 20px;
            background: #F0F2F5;
        }
        .features-inner { max-width: 1100px; margin: 0 auto; }
        .section-label {
            text-align: center; font-size: 0.78rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
            color: #10B981; margin-bottom: 14px;
        }
        .section-title {
            text-align: center; font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800; color: #0F172A; margin-bottom: 12px;
            letter-spacing: -0.01em;
        }
        .section-sub {
            text-align: center; color: #64748B; font-size: 1rem;
            max-width: 500px; margin: 0 auto 56px; line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .feature-card {
            background: #fff; border-radius: 16px;
            padding: 28px 30px;
            border: 1px solid #E8ECF0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
        .feature-ico {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin-bottom: 18px;
        }
        .feature-ico-green { background: #D1FAE5; }
        .feature-ico-blue { background: #DBEAFE; }
        .feature-ico-amber { background: #FEF3C7; }
        .feature-card h3 { font-size: 1.05rem; font-weight: 700; color: #0F172A; margin-bottom: 10px; }
        .feature-card p { color: #64748B; font-size: 0.9rem; line-height: 1.65; }

        .cta-section {
            background: #0F172A; padding: 80px 20px; text-align: center;
        }
        .cta-section h2 { font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; color: #fff; margin-bottom: 14px; }
        .cta-section p { color: #94A3B8; font-size: 1rem; margin-bottom: 32px; }

        .footer {
            background: #0F172A; padding: 24px 20px;
            border-top: 1px solid rgba(255,255,255,0.06);
            text-align: center;
        }
        .footer p { color: #475569; font-size: 0.825rem; }

        @media (max-width: 640px) {
            .nav { padding: 14px 20px; }
            .hero-stats { gap: 28px; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
    <div class="nav-brand">
        <div class="nav-brand-ico">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
        <span class="nav-brand-name">BudgetWatch</span>
    </div>
    <div class="nav-links">
        <a href="#features" class="nav-link">Features</a>
        <a href="login.php" class="nav-link">Login</a>
        <a href="register.php" class="nav-btn">Get Started</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div style="position:relative;z-index:1;">
        <div class="hero-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/></svg>
            Smart Finance Tracking
        </div>
        <h1>Master Your <span>Financial</span><br>Future Today</h1>
        <p>Track income, manage budgets, and achieve your savings goals — all in one beautiful, easy-to-use dashboard.</p>
        <div class="hero-btns">
            <a href="register.php" class="btn-hero-primary">
                Get Started Free
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="login.php" class="btn-hero-secondary">Sign In</a>
        </div>
        <div class="hero-stats">
            <div>
                <div class="hero-stat-num">₱0 <span style="color:#10B981">fees</span></div>
                <div class="hero-stat-lbl">Completely Free</div>
            </div>
            <div style="width:1px;background:rgba(255,255,255,0.08);"></div>
            <div>
                <div class="hero-stat-num">Real-<span style="color:#10B981">time</span></div>
                <div class="hero-stat-lbl">Live Updates</div>
            </div>
            <div style="width:1px;background:rgba(255,255,255,0.08);"></div>
            <div>
                <div class="hero-stat-num">100<span style="color:#10B981">%</span></div>
                <div class="hero-stat-lbl">Secure & Private</div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features-section" id="features">
    <div class="features-inner">
        <p class="section-label">Why BudgetWatch?</p>
        <h2 class="section-title">Everything you need to stay on track</h2>
        <p class="section-sub">Simple, powerful tools designed to help Filipinos take control of their money.</p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-ico feature-ico-green">📊</div>
                <h3>Track Everything</h3>
                <p>Seamlessly log every income and expense. Categorize transactions and get a clear picture of where your money goes.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico feature-ico-blue">📈</div>
                <h3>Visual Analytics</h3>
                <p>Beautiful real-time charts powered by Chart.js. Understand your spending trends at a glance, not a spreadsheet.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico feature-ico-amber">🎯</div>
                <h3>Budget Goals</h3>
                <p>Set monthly budget limits per category and get alerts when you're approaching or exceeding your limits.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico" style="background:#F3E8FF;">🔐</div>
                <h3>Secure by Design</h3>
                <p>Your data is protected with hashed passwords, PDO prepared statements, and session-based authentication.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico" style="background:#FEE2E2;">⚡</div>
                <h3>Real-Time Updates</h3>
                <p>Add or delete transactions instantly via AJAX. No page reloads — your dashboard updates live as you work.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico" style="background:#E0F2FE;">📱</div>
                <h3>Mobile Friendly</h3>
                <p>Fully responsive layout that works beautifully on your phone, tablet, or desktop. Manage finances anywhere.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <h2>Ready to take control<br>of your finances?</h2>
    <p>Join for free — no credit card, no limits.</p>
    <a href="register.php" class="btn-hero-primary" style="display:inline-flex;">
        Create Free Account
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
</section>

<!-- FOOTER -->
<footer class="footer">
    <p>© 2026 BudgetWatch. Built with PHP, MySQL & Chart.js.</p>
</footer>

</body>
</html>