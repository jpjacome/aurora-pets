Aurora Pets

Aurora Pets is a small Laravel-based web application for plant and pet care profiles, quizzes (PlantScan), and user-managed care recommendations. It combines server-side Blade views with GSAP-powered frontend interactions for a smooth multi-section mobile experience.

This repository contains the full application sources (backend + frontend assets) used in development.

## Features

- Multi-section PlantScan flow with GSAP ScrollTrigger/ScrollSmoother animations and mobile fallbacks
- Digital profiles for pets and plants (CRUD via Eloquent models)
- Email generation for PlantScan results and OG image generation job
- Admin pages for managing plants, pets and users

## Tech stack

- PHP 8.2+ and Laravel
- Blade templating
- GSAP (ScrollTrigger, ScrollSmoother) for advanced scrolling/animations
- Vanilla JS for progressive behavior and mobile fixes
- npm/Vite for frontend asset building

## Quick start (development)

1. Install PHP and Composer, Node.js and npm.
2. From project root:

```powershell
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build # or `npm run dev` for local hot rebuild
php artisan serve --host=127.0.0.1 --port=8000
# then open http://127.0.0.1:8000
```

3. If you use a database, update `.env` with DB credentials and run migrations:

```powershell
php artisan migrate --seed
```

## Mobile viewport & PlantScan notes

The PlantScan flow uses full-viewport stacked sections and GSAP pinning. Mobile browsers that hide/show the address/menu bar can change the viewport height and make pinned sections appear to "drop". To mitigate this the project includes two safeguards in `public/js/prevention.js`:

- Prefer using the Visual Viewport API to compute the CSS `--vh` variable on mobile and refresh GSAP calculations when the visual viewport changes.
- An optional, feature-flagged helper to disable native scrolling on mobile and drive navigation with buttons. Toggle the behavior with the variable `MOBILE_DISABLE_NATIVE_SCROLL_ENABLED` in `public/js/prevention.js`.

Please test on iOS Safari and Android Chrome when evaluating mobile behavior.

## Large assets / Git LFS

This repository contains several large image assets. GitHub may warn about files >50MB. Consider using Git LFS for these files:

- https://git-lfs.github.com/

If you want, add Git LFS tracking for image types used in `public/assets` (e.g. `git lfs track "public/assets/**/*.png"`) and re-push.

## Contributing

1. Fork and create a feature branch.
2. Make changes and run tests (if any).
3. Open a PR with a clear description.

## License

MIT

---

Bienvenido a Aurora Pets — si quieres que deje el README en español completo o que añada ejemplos de endpoints, dímelo y lo actualizo.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
