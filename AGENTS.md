# AGENTS.md - Development Guidelines

## Build/Lint/Test Commands

```bash
# Development (server + queue + vite)
composer run dev

# Individual services
php artisan serve
php artisan queue:listen --tries=1
npm run dev

# Testing
composer test              # Run all tests
php artisan test           # Alternative test command
php artisan test --filter=TestName  # Run single test
vendor/bin/pest --filter=TestName  # Run single Pest test

# Code Quality
vendor/bin/pint --dirty    # Format code (Laravel Pint)
vendor/bin/pint            # Format all code
npm run build             # Build frontend assets
```

## Code Style Guidelines

### PHP/Laravel Conventions
- **PSR-12** coding standards (4 spaces, LF line endings)
- **Strict typing**: Use `declare(strict_types=1);` and type hints
- **Laravel naming**: PascalCase for classes, camelCase for methods/variables
- **Eloquent over raw SQL**: Use models and relationships
- **Form Requests**: Validate in dedicated Form Request classes
- **Service Classes**: Complex business logic in dedicated service classes

### Testing (Pest PHP)
- **Feature tests** for HTTP endpoints and user flows
- **Unit tests** for business logic and utilities
- **Arrange-Act-Assert** pattern
- **Descriptive test names** in `it()` format

### Frontend (Vite + TailwindCSS 4)
- **Component-based**: Reusable Blade components
- **TailwindCSS 4**: Utility-first CSS approach
- **Alpine.js**: For interactive client-side behavior

### Database
- **Migration-driven**: All schema changes via migrations
- **Factory patterns**: Use factories for test data
- **Soft deletes**: For critical data preservation
- **Foreign key constraints**: Always define relationships

### Security
- **CSRF protection** on all forms
- **Authorization policies** for resource access
- **Input validation** using Form Requests
- **Never commit secrets** to repository