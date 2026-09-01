# 🔒 Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| latest  | ✅ Yes             |

---

## 🚨 Reporting a Vulnerability

We take the security of **Shopora** seriously. If you discover a security vulnerability, please follow the responsible disclosure process below.

### ⛔ Do NOT open a public issue

Security vulnerabilities must **never** be reported through public GitHub issues, discussions, or pull requests. Public disclosure of a vulnerability before a fix is available puts all users at risk.

### ✅ How to report

Send an email to:

**security@shopora.ir** *(replace with your actual address)*

Include the following details:

1. **Type of vulnerability** — e.g., RBAC bypass, XSS, SQL injection, CSRF, SSRF, authentication bypass, privilege escalation, etc.
2. **Affected component** — route, controller, API endpoint, or frontend module.
3. **Steps to reproduce** — a clear, minimal proof of concept.
4. **Impact assessment** — what data or functionality an attacker could access or modify.
5. **Suggested fix** (optional) — if you have a recommended remediation.

### 📧 Email Subject Format

```
[Shopora Security] <vulnerability-type> — <short description>
```

Example:
```
[Shopora Security] RBAC Bypass — Admin route accessible without role check
```

---

## 📬 What to Expect

| Stage | Timeline |
| --- | --- |
| Acknowledgment of your report | Within **48 hours** |
| Initial assessment & triage | Within **5 business days** |
| Fix developed & tested | Within **14 business days** (varies by severity) |
| Public disclosure (after patch) | Coordinated with reporter |

We will keep you informed throughout the process and credit you (unless you prefer anonymity) once the fix is released.

---

## 🛡️ Scope

The following are considered in-scope:

- **Authentication & authorization** bypass (e.g., accessing admin routes without login or correct role)
- **Cross-Site Scripting (XSS)** in admin panel or storefront
- **SQL Injection** or other injection attacks
- **CSRF** on state-changing endpoints
- **Server-Side Request Forgery (SSRF)**
- **Privilege escalation** (e.g., regular user gaining admin access)
- **Insecure Direct Object Reference (IDOR)** exposing other users' data
- **Sensitive data exposure** (API keys, passwords, tokens in responses)

---

## 🔑 Out of Scope

- Denial-of-Service (DoS) attacks
- Social engineering / phishing
- Issues in third-party dependencies (report upstream or via Dependabot)
- Theoretical attacks without a working proof of concept

---

## 🏆 Recognition

We appreciate the efforts of security researchers. With your permission, we will acknowledge your contribution in the release notes after the fix is deployed.

---

*Thank you for helping keep Shopora and its users safe.*
