<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <link rel="apple-touch-icon" href="/assets/favicon.png">
    
    <link rel="stylesheet" href="/css/aurora-general.css">
    <link rel="stylesheet" href="/css/admin-style.css">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <header class="admin-header">
        <div class="admin-left">
            <a href="/">
                <img src="/assets/home/imgs/logo4.png" alt="logo" class="admin-logo">
            </a>

            <nav class="admin-nav">
                <a href="/admin">Dashboard</a>
                <a href="/admin/chatbot">Chatbot</a>
                <a href="/admin/tests">Tests</a>

                <div class="nav-dropdown">
                    <button class="nav-dropdown-button" aria-haspopup="true" aria-expanded="false">Database <i class="ph ph-caret-down"></i></button>
                    <div class="nav-dropdown-menu" role="menu" aria-label="Database menu">
                        <a href="/admin/plants" role="menuitem">Plants</a>
                        <a href="/admin/users" role="menuitem">Users</a>
                        <a href="/admin/clients" role="menuitem">Clients</a>
                    </div>
                </div>

                <a href="/admin/email-campaigns">Email Campaigns</a>
                <!-- Keep mobile links present in the mobile menu; desktop-only dropdown added above -->
            </nav>
        </div>
        <div class="admin-right">
            <div class="admin-profile-dropdown">
                <button class="admin-profile-button" id="profileDropdownButton">
                    <i class="ph ph-user-circle"></i>
                </button>
                <div class="admin-dropdown-menu" id="profileDropdownMenu">
                    <a href="/admin/settings" class="admin-dropdown-item">
                        <i class="ph ph-gear"></i>
                        <span>Admin Settings</span>
                    </a>
                    <form method="POST" action="/logout" class="logout-form">
                        @csrf
                        <button type="submit" class="admin-dropdown-item logout-dropdown-button">
                            <i class="ph ph-sign-out"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>                            <!-- Mobile hamburger (shown on small screens) -->
                <button id="adminHamburgerButton" class="admin-hamburger" aria-label="Open menu" aria-controls="adminMobileMenu" aria-expanded="false">
                    <span class="hamburger-inner" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </header>

    <main class="admin-container">
        @yield('content')
    </main>

    <!-- Mobile fullscreen menu overlay -->
    <div id="adminMobileMenu" class="mobile-menu-overlay" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="mobile-menu-content">
            <button id="adminMobileMenuClose" class="mobile-menu-close" aria-label="Close menu"><i class="ph ph-x"></i></button>
            <nav class="mobile-nav">
                <a href="/admin">Dashboard</a>
                <a href="/admin/chatbot">Chatbot</a>
                <a href="/admin/tests">Tests</a>
                <a href="/admin/clients">Clients</a>
                <a href="/admin/email-campaigns">Email Campaigns</a>
                <a href="/admin/plants">Plants</a>
                <a href="/admin/users">Users</a>
                <a href="/admin/settings">Admin Settings</a>
                <form method="POST" action="/logout" class="logout-form">
                    @csrf
                    <button type="submit" class="admin-dropdown-item logout-dropdown-button admin-mobile-logout">Logout</button>
                </form>
            </nav>
        </div>
    </div>

    <script>
        // Profile dropdown functionality
        const profileButton = document.getElementById('profileDropdownButton');
        const profileMenu = document.getElementById('profileDropdownMenu');

        profileButton.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!profileButton.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.remove('show');
            }
        });

        // Mobile menu functionality
        (function() {
            const btn = document.getElementById('adminHamburgerButton');
            const menu = document.getElementById('adminMobileMenu');
            const closeBtn = document.getElementById('adminMobileMenuClose');

            if (!btn || !menu) return;

            function openMenu() {
                btn.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
                menu.classList.add('active');
                menu.setAttribute('aria-hidden', 'false');
                document.body.classList.add('no-scroll');
            }

            function closeMenu() {
                btn.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
                menu.classList.remove('active');
                menu.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('no-scroll');
            }

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = btn.classList.contains('open');
                if (isOpen) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            // Close by clicking the overlay (but not the content)
            menu.addEventListener('click', (e) => {
                if (e.target === menu) closeMenu();
            });

            if (closeBtn) closeBtn.addEventListener('click', closeMenu);

            // Close on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeMenu();
                }
            });
        })();
    </script>
    @yield('scripts')
</body>
</html>