# Contributing to LoveGem

Thank you for considering contributing to LoveGem Framework! 🎉

## How to Contribute

### 1. Fork the Repository
```bash
# Click the Fork button on GitHub
git clone https://github.com/your-username/lovegem-framework.git
cd lovegem-framework
```

### 2. Create a Branch
```bash
# For bug fixes
git checkout -b fix/issue-number-description

# For new features
git checkout -b feature/feature-name

# For documentation
git checkout -b docs/fix-description
```

### 3. Install Dependencies
```bash
composer install
```

### 4. Make Your Changes
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation if needed

### 5. Test Your Changes
```bash
php vendor/bin/phpunit
php vendor/bin/phpstan analyse
```

### 6. Commit Your Changes
```bash
git add .
git commit -m "feat: add amazing feature"
git push origin feature/feature-name
```

## Commit Message Convention

| Prefix | Description |
|--------|-------------|
| `feat:` | New feature |
| `fix:` | Bug fix |
| `docs:` | Documentation |
| `style:` | Formatting |
| `refactor:` | Code refactoring |
| `test:` | Tests |
| `chore:` | Maintenance |

## Code Style

- Follow PSR-12 standard
- Use strict types: `declare(strict_types=1);`
- Use type hints for all parameters
- Use return type declarations
- Keep methods short and focused

## Reporting Issues

- Use the GitHub issue tracker
- Include PHP version
- Include error messages
- Provide reproduction steps

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Questions?

Open a discussion on GitHub!

---

**Thank you for making LoveGem better!** 🚀
