<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - @yield('title')</title>
    
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
            <img src="/assets/home/imgs/logo4.png" alt="logo" class="admin-logo">
            <nav class="admin-nav">
                <a href="/">Homepage</a>
                <a href="/admin">Dashboard</a>
                <a href="/admin/tests">Tests</a>
                <a href="/admin/clients">Clients</a>
                <a href="/admin/plants">Plants</a>
                <a href="/admin/users">Users</a>
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
                </div>
            </div>
        </div>
    </header>

    <main class="admin-container">
        @yield('content')
    </main>

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
    </script>
</body>
</html>
