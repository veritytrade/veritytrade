# Dev checklist (before commit)

- **Dead links:** Grep `route(` and `href=` in `resources/views` and confirm each route name exists in `routes/web.php` or `routes/auth.php` (and Phones module routes).
- **Conflicts / logic:** Ensure no duplicate or contradictory checks (e.g. middleware vs controller, OR vs AND for login).
- **Bugs:** Run `php artisan route:list`, fix any PHP/Blade linter errors, and test critical flows (login, profile, orders, invoices).
- **Commit:** Stage, commit with a clear message, then **push to GitHub** (`git push`).
