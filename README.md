# Barkea Resort Web Application

A secure PHP/MySQL web application for managing resort villas, construction works, work assignments, materials, warehouse stock, suppliers, teams, users, and reports.

This README describes how to prepare an **Ubuntu-based application server**, deploy the project, configure its database and HTTPS, enable logging, and access the local development site from Windows.

> **Deployment scope:** The paths and hostname below document the tested Barkea Resort local deployment. Adjust them for another environment. Do not publish the local development certificate or credentials as production secrets.

## 1. Project Structure

```text
barkea-resort/
├── config/          # Application, environment, database and role configuration
├── controllers/     # HTTP request handling
├── database/        # Database schema and seed scripts
├── public/          # Apache DocumentRoot; public/index.php is the entry point
├── repositories/    # PDO database access
├── scripts/         # initial administrator script
├── security/        # Authentication, authorization, sessions, CSRF and errors
├── services/        # Business logic
├── tests/           # PHP test scripts
├── util/            # Validation, application logging and audit logging
├── views/           # UI templates and pages
├── .env.example     # Environment configuration template
└── README.md
```

Only `public/` should be exposed as the web document root. The actual source tree may contain additional static assets and templates under these directories.

## 2. Features and Roles

The application covers villa and work management, individual or team work assignments, materials and suppliers, warehouse stock and movements, user and team management, and reports.

The database defines three user roles: `admin`, `manager`, and `staff`. Access to individual actions is controlled by server-side authorization. Refer to `config/roles.php` and the security layer for the exact permission rules.

## 3. Naming Conventions

- **PHP classes/files:** PascalCase, e.g. `WorkController.php`, `StockRepository.php`.
- **Methods/variables:** camelCase, e.g. `findById()`, `$workId`.
- **Database tables/columns:** snake_case, e.g. `villa_groups`, `stock_movements`, `created_at`.
- **Directories:** lowercase, e.g. `controllers/`, `repositories/`, `security/`.

## 4. Requirements and Ubuntu Installation

Use a supported Ubuntu LTS release with the following software:

- Apache HTTP Server 2.4+
- PHP 8.x with PDO/MySQL support
- MySQL Server (the deployment uses MySQL 8-compatible SQL)
- Git, OpenSSL, and `logrotate`
- An SSH client for remote access, if deploying from another computer

The original development environment used PHP 8.5.4; Ubuntu's default PHP version depends on the release and configured repositories. Verify compatibility before choosing a different PHP version.

### 4.1 Update Ubuntu

```bash
sudo apt update
sudo apt upgrade -y
```

### 4.2 Install Apache

```bash
sudo apt install -y apache2
sudo systemctl enable --now apache2
apache2 -v
sudo systemctl status apache2
```

### 4.3 Install PHP and extensions

```bash
sudo apt install -y php libapache2-mod-php php-cli php-mysql php-mbstring php-xml
php -v
php -m | grep -Ei 'PDO|pdo_mysql|mbstring'
```

Ensure `PDO` and `pdo_mysql` are listed. The application uses PDO prepared statements for database access.

### 4.4 Install MySQL

```bash
sudo apt install -y mysql-server
sudo systemctl enable --now mysql
mysql --version
sudo systemctl status mysql
```

Review the MySQL server's security settings. On installations that provide it, `sudo mysql_secure_installation` can assist with initial hardening.

### 4.5 Install supporting tools and enable Apache modules

```bash
sudo apt install -y git openssl logrotate
sudo a2enmod rewrite ssl headers
sudo apache2ctl configtest
sudo systemctl restart apache2
```

If your Ubuntu release or Apache PHP integration differs, adjust the packages accordingly.

## 5. Obtain and Configure the Application

### 5.1 Deploy the project

Clone the private repository using an authorized account:

Extract the supplied project archive into `/var/www/html/barkea-resort`. Replace `<REPOSITORY_URL>` with the actual authorized clone URL. Arrange file ownership and permissions according to your deployment policy; do not make the whole source tree world-writable.

### 5.2 Configure `.env`

```bash
cd /var/www/html/barkea-resort
cp .env.example .env
nano .env
```
Populate the environment variables **using the exact names provided by `.env.example`**, including the database connection values. The configuration code is in `config/`.

- `.env.example` is tracked by Git and must contain no real secrets.
- `.env` contains local values and **must not** be committed or included in the submission archive.
- Restrict access to `.env` to the account(s) that need to read it. Never place it under `public/`.
- Do not commit passwords, TLS private keys, or other secrets.

To check whether either file is tracked:

```bash
git ls-files .env .env.example
```
The verified project configuration tracks `.env.example` but not `.env`.

## 6. Database Setup

The application database is named **`barkea_resort`**. The project provides:

- `database/database.sql`: schema definition; starts with `USE barkea_resort;`.
- `database/seed.sql`: initial data for **4 villa groups, 8 villa typologies, and 34 villas**.

Perform these steps on a **new/empty database**. Do not re-import the schema or seed script over an existing installation without reviewing their effects.

### 6.1 Create the database and application user

```bash
sudo mysql
```
In the MySQL prompt:
```sql
CREATE DATABASE barkea_resort
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'barkea_app'@'localhost'
  IDENTIFIED BY 'REPLACE_WITH_A_STRONG_UNIQUE_PASSWORD';

GRANT SELECT, INSERT, UPDATE, DELETE
  ON barkea_resort.* TO 'barkea_app'@'localhost';

EXIT;
```
Use a strong unique password. The application account is for routine data operations; schema import uses an administrative account. If deployment requires additional privileges, review the exact application operations before granting them.

### 6.2 Import schema and initial data

From the project root:

```bash
cd /var/www/html/barkea-resort
sudo mysql < database/database.sql
sudo mysql barkea_resort < database/seed.sql
```

The database must exist before the schema import because the schema script starts with `USE barkea_resort;`. Configure the corresponding database username, password, host, and database name in `.env` using its provided variable names.

### 6.3 Verify database connectivity

```bash
php tests/test_database.php
```

Review the test output. **Other test scripts may insert, update, or delete data**; run those against an isolated test database, not the live application database.

## 6.3 Initial Administrator Account

After importing the database schema and seed data, create the initial administrator account using the provided CLI setup script.

From the project root, execute:

```bash
php scripts/create_admin.php
```

The script checks whether an administrator already exists, requests a password with a minimum length of 12 characters, securely hashes it using PHP's `password_hash()` function, and creates an active administrator through the application's `UserRepository`.

The script must be executed only by an authorized administrator from the server terminal.

After successful initialization, the script should be removed from the deployed application:

```bash
rm scripts/create_admin.php
```

The original setup script remains available in the project distribution for future fresh installations. Do not run it against an existing production database to recreate administrator accounts.

### 2. Execute the Script

From the project root:

```bash
php tests/create_admin.php
```

Follow the terminal prompts to create the administrator account.

### 3. Verify the Administrator

Verify that the account exists:

```bash
sudo mysql barkea_resort -e \
"SELECT id, username, role, status FROM users WHERE role = 'admin';"
```

The account should have the role `admin` and status `active`.

### 4. Remove the Temporary Script

After successful account creation, remove the script:

```bash
rm tests/create_admin.php
```
The administrator creation script must not remain accessible in the deployed project or be committed to Git.

### Log In

Open:
```text
https://barkea.local
```
Sign in using the administrator credentials created during initialization.

**Security note:** Never store plaintext administrator passwords in the repository, database scripts, README, or version-control history.

## 7. Apache and HTTPS Configuration

The verified local deployment uses:

- Hostname: `barkea.local` (alias `www.barkea.local`)
- HTTP: port `80`, redirected to HTTPS
- HTTPS: port `443`
- DocumentRoot: `/var/www/html/barkea-resort/public`
- VirtualHost file: `/etc/apache2/sites-available/barkea.conf` (enabled via `sites-enabled`)

### 7.1 Configure the VirtualHost

Create or edit the configuration:

```bash
sudo nano /etc/apache2/sites-available/barkea.conf
```

The following reproduces the verified local setup **after** a suitable local TLS certificate and key have been created:

```apache
<VirtualHost *:80>
    ServerName barkea.local
    ServerAlias www.barkea.local
    Redirect permanent / https://barkea.local/
</VirtualHost>

<VirtualHost *:443>
    ServerName barkea.local
    ServerAlias www.barkea.local
    DocumentRoot /var/www/html/barkea-resort/public

    <Directory /var/www/html/barkea-resort/public>
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile /etc/ssl/barkea-resort/barkea.local.crt
    SSLCertificateKeyFile /etc/ssl/barkea-resort/barkea.local.key

    ErrorLog ${APACHE_LOG_DIR}/barkea_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/barkea_ssl_access.log combined

    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; img-src 'self' data:; font-src 'self' data:; style-src 'self'; script-src 'self'; connect-src 'self'"
</VirtualHost>
```

**Note:** The `Content-Security-Policy` header above is **enforced**, not report-only. Review it if new third-party assets are introduced.

### 7.2 Create a development TLS certificate on a new server

The original server already has a local certificate. For a **new isolated development environment**, one way to create a self-signed certificate with the required SAN entries is:

```bash
sudo install -d -m 755 /etc/ssl/barkea-resort
sudo openssl req -x509 -newkey rsa:3072 -sha256 -days 365 -nodes \
  -keyout /etc/ssl/barkea-resort/barkea.local.key \
  -out /etc/ssl/barkea-resort/barkea.local.crt \
  -subj '/CN=barkea.local' \
  -addext 'subjectAltName=DNS:barkea.local,DNS:www.barkea.local' \
  -addext 'basicConstraints=critical,CA:FALSE' \
  -addext 'keyUsage=critical,digitalSignature,keyEncipherment' \
  -addext 'extendedKeyUsage=serverAuth'

sudo chown root:root /etc/ssl/barkea-resort/barkea.local.*
sudo chmod 600 /etc/ssl/barkea-resort/barkea.local.key
sudo chmod 644 /etc/ssl/barkea-resort/barkea.local.crt
```

The key must remain private on the server. This is a **development-only** certificate; for a more maintainable local PKI, create a dedicated development CA, trust its CA certificate on clients, and issue a separate server certificate. Do not import an arbitrary server certificate as a trusted root.

### 7.3 Enable and verify the site

```bash
sudo a2ensite barkea.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
sudo apache2ctl -S
```

Verify that `barkea.local` is configured on ports 80 and 443 and that HTTPS serves the application's `public/` directory. If the site's name is already enabled, `a2ensite` may report that it is already enabled.

## 8. Windows Hostname Resolution and Certificate Trust

The following steps apply when accessing the local Ubuntu VM from a Windows computer.

### 8.1 Configure the Windows hosts file

Run Notepad as Administrator and open:

```text
C:\Windows\System32\drivers\etc\hosts
```

Add a line with the **current IP address** of the Ubuntu VM:

```text
<SERVER_IP> barkea.local www.barkea.local
```

Save the file. The VM must be reachable from Windows on port 443.

### 8.2 Copy the public certificate to Windows

From Windows PowerShell:

```powershell
scp cyber@<SERVER_IP>:/etc/ssl/barkea-resort/barkea.local.crt "$env:USERPROFILE\Downloads\barkea.local.crt"
```

Replace `<SERVER_IP>` with the VM's actual address and `cyber` with the SSH username if different. **Never transfer `barkea.local.key` to Windows.**

### 8.3 Verify the certificate before trusting it

On Ubuntu:

```bash
openssl x509 -in /etc/ssl/barkea-resort/barkea.local.crt -noout -fingerprint -sha256
openssl x509 -in /etc/ssl/barkea-resort/barkea.local.crt -noout -subject -issuer -dates -ext subjectAltName
```

On Windows:

```powershell
certutil -hashfile "$env:USERPROFILE\Downloads\barkea.local.crt" SHA256
```

Compare the SHA-256 fingerprints using a trusted channel. The original deployment certificate has `CN=barkea.local` for both subject and issuer and SAN entries for `barkea.local` and `www.barkea.local`; its recorded validity period is **October 16, 2026–October 16, 2027**. A newly generated certificate will have different dates and fingerprint.

### 8.4 Trust the development certificate appropriately

**Preferred:** If using a dedicated local CA, import the **CA certificate** into Windows **Trusted Root Certification Authorities**, then use the server certificate issued by that CA.

For the original self-issued local development certificate, Windows trust may be established only after confirming the certificate is appropriate for the local setup and understanding the security implications. Do not blindly import an unknown certificate into Trusted Root Certification Authorities. To inspect the current-user certificate store, run `certmgr.msc` and open **Trusted Root Certification Authorities → Certificates**. Use **All Tasks → Import** only for a verified trust anchor. A server certificate explicitly marked `CA:FALSE` should not be used as a root CA; prefer a proper local CA for new deployments.

Restart browser if needed, then open:

```text
https://barkea.local
```

The browser should report a secure connection once hostname resolution, certificate validity, and trust are correctly configured. Do not bypass certificate warnings for routine use.

## 9. Application and Audit Logging

The application has **two separate logging destinations**, verified from `public/index.php`:

| File | Purpose |
|---|---|
| `/var/log/barkea-resort/app.log` | General application `INFO`, `WARNING`, `ERROR`, and exception events |
| `/var/log/barkea-resort/audit.log` | Audit events, written with an `AUDIT_` prefix |

`util/Logger.php` defaults to `app.log`. `public/index.php` creates the audit logger with `new Logger('/var/log/barkea-resort/audit.log')`, passed to `util/AuditLogger.php`.

Audit events include selected metadata (e.g. acting user ID/role and affected entity). The logger redacts context fields whose keys indicate sensitive values such as passwords, tokens, secrets, cookies, and API keys. Do not treat this as a substitute for avoiding sensitive information in log messages.

### 9.1 Prepare log files and permissions

On a **new server**:

```bash
sudo mkdir -p /var/log/barkea-resort
sudo touch /var/log/barkea-resort/app.log /var/log/barkea-resort/audit.log
sudo chown www-data:www-data /var/log/barkea-resort
sudo chown www-data:www-data /var/log/barkea-resort/app.log /var/log/barkea-resort/audit.log
sudo chmod 750 /var/log/barkea-resort
sudo chmod 640 /var/log/barkea-resort/app.log /var/log/barkea-resort/audit.log
```

These files are outside the Apache document root. The `www-data` account must be able to append to them. Avoid running the PHP application as root.

### 9.2 Configure logrotate

Install if needed:

```bash
sudo apt install -y logrotate
sudo nano /etc/logrotate.d/barkea-resort
```

Use the **verified existing deployment configuration**:

```text
/var/log/barkea-resort/app.log
/var/log/barkea-resort/audit.log {
    daily
    rotate 14
    missingok
    notifempty
    compress
    delaycompress
    dateext
    create 0640 www-data www-data
}
```

This checks for daily rotation, keeps 14 rotated versions, compresses older rotations, and creates replacement files with restricted permissions. Retention is **14 rotations**, not necessarily exactly 14 calendar days if logs are empty or rotation is skipped.

### 9.3 Verify logrotate

```bash
sudo logrotate -d /etc/logrotate.d/barkea-resort
sudo ls -lah /var/log/barkea-resort/
```

`-d` performs a debug/dry run; it does not rotate logs. The original deployment already had rotated application and audit logs, so do not overwrite or delete existing log files as part of routine setup.

### 9.4 Apache HTTPS logs

Apache also writes:

```text
/var/log/apache2/barkea_ssl_access.log
/var/log/apache2/barkea_ssl_error.log
```

These are web-server logs, separate from the PHP application and audit logs.

## 10. Security Measures

The project includes security components for:

- Authentication, session handling, and role-based authorization.
- Server-side validation and PDO prepared statements.
- CSRF protection for state-changing requests.
- Error handling and application logging.
- Dedicated audit events for selected actions.
- HTTPS, HTTP-to-HTTPS redirection, and security headers.
- Restricting the web document root to `public/`.
- Keeping environment secrets and log files outside public access.

Security controls must be tested in the deployed environment. A locally self-signed TLS certificate is intended for development, not public internet deployment.

## 11. Testing and Verification

Test scripts are stored in `tests/`, including tests for authentication, authorization, CSRF, validation, sessions, repositories, and services.

Examples:

```bash
php tests/test_database.php
php tests/test_validator.php
php tests/test_csrf.php
php tests/test_authorization.php
```

Some service/repository tests may modify the database. Run them **only against an isolated test database**, review their prerequisites, and verify their results before treating them as passing. A successful PHP syntax check does not replace functional testing.

To check PHP syntax on an individual file:

```bash
php -l public/index.php
```

To check Apache configuration:

```bash
sudo apache2ctl configtest
sudo apache2ctl -S
```

To check deployment services:

```bash
sudo systemctl status apache2
sudo systemctl status mysql
```

## 12. Known Deployment Considerations

- The hostname `barkea.local` and its hosts-file mapping are intended for a local environment; the VM IP can change.
- The development TLS certificate must be trusted appropriately on each client and renewed when expired.
- MySQL account privileges should remain limited to what the application needs.
- Database-dependent tests should use an isolated test database.
- The creation of the initial administrator account must be documented separately once its supported provisioning method is verified.
- The project archive does not include system configuration, local secrets, certificates, or runtime logs.

---

**Project:** Barkea Resort Web Application  
**Stack:** PHP, MySQL, Apache, HTML/CSS/JavaScript  
**Environment:** Ubuntu application server with local HTTPS deployment
