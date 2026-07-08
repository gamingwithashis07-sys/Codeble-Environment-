# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability within LoveGem, please send an email to security@lovegem.dev. All security vulnerabilities will be promptly addressed.

**Please do NOT report security vulnerabilities through public GitHub issues.**

## Security Policy

### Supported Versions

| Version | Supported |
|---------|-----------|
| 1.0.x   | ✅ Yes |

### What we consider a security vulnerability

- Remote Code Execution (RCE)
- SQL Injection
- Cross-Site Scripting (XSS)
- Cross-Site Request Forgery (CSRF)
- Authentication Bypass
- Authorization Bypass
- Cryptographic Weaknesses
- Data Leakage
- Insecure Deserialization

### Security Best Practices in LoveGem

- **Encryption by Default**: All data encrypted using libsodium
- **CSRF Protection**: Built-in CSRF tokens
- **SQL Injection Prevention**: Query Builder with parameter binding
- **XSS Prevention**: Blade auto-escaping
- **Secure Password Hashing**: bcrypt/Argon2
- **Rate Limiting**: Built-in rate limiter
- **Security Headers**: Automatic security headers

## Response Timeline

- **Initial Response**: Within 24 hours
- **Status Update**: Within 72 hours
- **Security Patch**: Within 7 days (critical)

## Disclosure Policy

We follow responsible disclosure:

1. **Reporter** notifies us privately
2. **We** acknowledge and investigate
3. **We** develop a fix
4. **We** release the fix
5. **We** publicly disclose after fix is available

## Security Measures

### Built-in Protection

```php
// LoveGem automatically provides:

// 1. CSRF Protection
Route::middleware(['csrf']);

// 2. Encryption
$encrypted = EncryptionService::encrypt($data);

// 3. Password Hashing
$hash = Hash::make($password);

// 4. SQL Injection Prevention
User::where('email', $email)->first();

// 5. XSS Prevention (Blade)
{!! $safeOutput !!}  // Escaped output
```

### Privacy First

- No tracking without consent
- Data minimization
- Right to be forgotten
- GDPR compliance tools

## Contact

- **Security Email**: security@lovegem.dev
- **PGP Key**: Available on request
- **GitHub**: [https://github.com/lovegem-framework/lovegem](https://github.com/lovegem-framework/lovegem)

## Thank You

We appreciate responsible disclosure and will credit security researchers who help us maintain the security of LoveGem.

---

**LoveGem Framework** - Security First! 🔐
