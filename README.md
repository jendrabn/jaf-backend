# JAF Store — Back Office Admin & API E‑Commerce

Aplikasi Laravel untuk mengelola katalog produk, pesanan, pembayaran, promosi, blog, newsletter, dan manajemen pengguna. Menyediakan panel admin lengkap serta API untuk frontend (FRONT_URL).

Tech stack:
- PHP 8.4, Laravel 12 (Sanctum, Socialite)
- MySQL, Redis (cache, session, queue)
- Spatie Permission, Spatie Media Library
- Yajra DataTables, DomPDF, Snappy (wkhtmltopdf), Intervention Image
- Midtrans Payment Gateway
- Vite, Node.js (LTS), SCSS, Bootstrap/AdminLTE

Repo: https://github.com/jendrabn/jaf

## Daftar Menu Admin

- Dashboard: Ringkasan metrik toko, penjualan, orders pending.
- User: CRUD pengguna, aktivasi/nonaktif, role assignment dasar.
- Role & Permission: Kelola role dan permission berbasis Spatie.
- Order: Daftar pesanan, detail, status (pending, paid, shipped), cetak invoice.
- Shop:
  - Product: CRUD produk, gambar, media, stok, harga, diskon.
  - Category: CRUD kategori produk, hierarki.
  - Brand: CRUD brand/merk.
  - Courier: Konfigurasi ekspedisi dan ongkir.
  - Coupon: Kupon diskon, masa berlaku, limit, relasi produk.
  - Tax: Pajak/PPN.
- Payment Method:
  - Bank: Konfigurasi bank transfer.
  - E-Wallet: Konfigurasi e-wallet (OVO, GoPay, dsb).
- Banner: Manajemen banner/hero untuk promosi.
- Blog:
  - Blog: Artikel/blog post CRUD.
  - Category: Kategori blog.
  - Tag: Tag/label artikel.
- Newsletter:
  - Subscribers: Manajemen pelanggan newsletter.
  - Campaigns: Pengiriman kampanye email.
- Contact Messages: Pesan masuk dari formulir kontak.
- Audit Log: Catatan aktivitas penting sistem.

## Instalasi & Menjalankan di Lokal (XAMPP atau Laragon)

Prasyarat:
- Windows dengan XAMPP atau Laragon terpasang.
- Composer
- Node.js LTS (npm)
- MySQL berjalan lokal (phpMyAdmin), Redis opsional di lokal.

### Menggunakan Laragon (Direkomendasikan)
1. Pastikan Laragon berjalan (Start All). Aktifkan Auto Virtual Hosts bila perlu.
2. Clone aplikasi ke direktori Laragon:
```bat
cd C:\laragon\www
git clone https://github.com/jendrabn/jaf.git jaf-store
cd jaf-store
copy .env.example .env
```
3. Buat database via HeidiSQL/phpMyAdmin (contoh: `laravel_jaf`) lalu sesuaikan `.env`:
```env
APP_NAME="JAF Parfum's"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://jaf-store.test
FRONT_URL=http://127.0.0.1:5173

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_jaf
DB_USERNAME=root
DB_PASSWORD=

# Gunakan Redis jika tersedia di Windows, jika tidak maka fallback ke database:
CACHE_STORE=redis         # atau 'database'
SESSION_DRIVER=redis      # atau 'database'
QUEUE_CONNECTION=redis    # atau 'database'

# Integrasi penting (isi sesuai kredensial lokal)
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT="${APP_URL}/auth/google/callback"
RAJAONGKIR_KEY=...
RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api/v1
MIDTRANS_SERVER_KEY=...
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=false
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=you@example.com

# Firebase Cloud Messaging (FCM) untuk Push Notification
# Get these values from Firebase Console > Project Settings > Service Accounts
FIREBASE_PROJECT_ID=your-firebase-project-id
FIREBASE_TYPE=service_account
FIREBASE_PRIVATE_KEY_ID=your-private-key-id
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYOUR_PRIVATE_KEY_HERE\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=your-service-account@your-project-id.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your-client-id
FIREBASE_AUTH_URI=https://accounts.google.com/o/oauth2/auth
FIREBASE_TOKEN_URI=https://oauth2.googleapis.com/token
FIREBASE_AUTH_PROVIDER_X509_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
FIREBASE_CLIENT_X509_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/your-service-account%40your-project-id.iam.gserviceaccount.com
FIREBASE_UNIVERSE_DOMAIN=googleapis.com
```
4. Install dependency & build aset:
```bat
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm ci
npm run dev   # atau npm run build
```
5. Akses aplikasi melalui Laragon Virtual Host: `http://jaf-store.test`.

> Catatan: Jika Auto Virtual Hosts belum aktif, atur Virtual Host melalui Laragon (Menu → Apache/Nginx → Virtual Hosts) dengan DocumentRoot mengarah ke `C:\laragon\www\jaf-store\public`.

### Menggunakan XAMPP
1. Jalankan Apache & MySQL di XAMPP.
2. Clone aplikasi ke direktori XAMPP:
```bat
cd C:\xampp\htdocs
git clone https://github.com/jendrabn/jaf.git jaf-store
cd jaf-store
copy .env.example .env
```
3. Buat database via phpMyAdmin (contoh: `laravel_jaf`) lalu sesuaikan `.env`:
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://jaf-store.local
FRONT_URL=http://127.0.0.1:5173

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_jaf
DB_USERNAME=root
DB_PASSWORD=
```
4. Install dependency & build aset:
```bat
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm ci
npm run dev   # atau npm run build
```
5. Konfigurasi Virtual Host Apache agar DocumentRoot mengarah ke `public`:
```apache
# C:\xampp\apache\conf\extra\httpd-vhosts.conf
<VirtualHost *:80>
    ServerName jaf-store.local
    DocumentRoot "C:/xampp/htdocs/jaf-store/public"
    <Directory "C:/xampp/htdocs/jaf-store/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Tambahkan hosts:
```text
# C:\Windows\System32\drivers\etc\hosts
127.0.0.1   jaf-store.local
```
Restart Apache, lalu akses `http://jaf-store.local`.

Opsional: Jalankan queue worker untuk proses async/email:
```bat
php artisan queue:work
```

## Deployment ke VPS Ubuntu (Production)

Contoh di Ubuntu 22.04/24.04 dengan domain store.jaf.co.id. Aplikasi akan ditempatkan di /var/www/jaf-store.

1) Persiapan sistem dan paket dasar
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx git curl unzip software-properties-common ufw supervisor
```

2) Instal PHP 8.4 + ekstensi
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4-fpm php8.4-cli php8.4-common php8.4-bcmath php8.4-ctype php8.4-curl \
  php8.4-dom php8.4-fileinfo php8.4-gd php8.4-intl php8.4-mbstring php8.4-mysql php8.4-opcache \
  php8.4-readline php8.4-redis php8.4-simplexml php8.4-tokenizer php8.4-xml php8.4-xmlwriter php8.4-zip
# Opsional (disarankan):
sudo apt install -y php-imagick wkhtmltopdf
```

3) Instal MySQL dan buat database
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
sudo mysql -u root <<'SQL'
CREATE DATABASE jaf_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'jaf'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON jaf_store.* TO 'jaf'@'localhost';
FLUSH PRIVILEGES;
SQL
```

4) Instal Redis
```bash
sudo apt install -y redis-server
sudo systemctl enable --now redis-server
```

5) Instal Composer (global)
```bash
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
```

6) Instal Node.js LTS
```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
node -v && npm -v
```

7) Clone aplikasi ke /var/www/jaf-store
```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/jendrabn/jaf.git jaf-store
cd /var/www/jaf-store
sudo cp .env.example .env
sudo chown -R $USER:$USER /var/www/jaf-store
```

Edit .env (Wajib):
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://store.jaf.co.id
FRONT_URL=https://store.jaf.co.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jaf_store
DB_USERNAME=jaf
DB_PASSWORD=strong_password_here

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT="${APP_URL}/auth/google/callback"
RAJAONGKIR_KEY=...
RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api/v1
MIDTRANS_SERVER_KEY=...
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=true
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@store.jaf.co.id
```

8) Install dependency & build aset
```bash
cd /var/www/jaf-store
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
npm ci
npm run build
```

9) Permission direktori
```bash
sudo chown -R www-data:www-data /var/www/jaf-store
sudo find /var/www/jaf-store/storage -type d -exec chmod 775 {} \;
sudo find /var/www/jaf-store/bootstrap/cache -type d -exec chmod 775 {} \;
```

10) Konfigurasi Nginx (php-fpm + FastCGI Cache)

Buat file cache config di level http:
```bash
sudo tee /etc/nginx/conf.d/fastcgi_cache.conf > /dev/null <<'NGX'
fastcgi_cache_path /var/cache/nginx/fastcgi levels=1:2 keys_zone=PHP_CACHE:100m inactive=60m max_size=1g;
map $request_method $skip_cache {
    default 0;
    POST 1;
    PUT 1;
    PATCH 1;
    DELETE 1;
}
map $http_cookie $no_cache_cookie {
    default 0;
    ~*"(XSRF-TOKEN|laravel_session)" 1;
}
NGX
```

Buat server block untuk domain:
```bash
sudo tee /etc/nginx/sites-available/jaf-store.conf > /dev/null <<'NGX'
server {
    server_name store.jaf.co.id www.store.jaf.co.id;
    root /var/www/jaf-store/public;
    index index.php;

    add_header X-Frame-Options SAMEORIGIN;
    add_header X-Content-Type-Options nosniff;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;

        fastcgi_cache_bypass $skip_cache $no_cache_cookie;
        fastcgi_no_cache $skip_cache $no_cache_cookie;
        fastcgi_cache PHP_CACHE;
        fastcgi_cache_valid 200 301 302 10m;
        add_header X-FastCGI-Cache $upstream_cache_status;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff2?)$ {
        expires 7d;
        access_log off;
        try_files $uri =404;
    }
}
NGX
```

Aktifkan site dan reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/jaf-store.conf /etc/nginx/sites-enabled/jaf-store.conf
sudo nginx -t && sudo systemctl reload nginx
```

11) Amankan dengan SSL (Certbot)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d store.jaf.co.id -d www.store.jaf.co.id --agree-tos -m admin@store.jaf.co.id --redirect -n
sudo systemctl reload nginx
```

12) Optimasi cache Laravel (production)
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

13) Setup Queue dengan Supervisor
```bash
sudo tee /etc/supervisor/conf.d/jaf-queue.conf > /dev/null <<'SUP'
[program:jaf-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/jaf-store/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/jaf-queue.log
stopwaitsecs=3600
SUP

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

14) Environment final check
- Pastikan APP_ENV=production dan APP_DEBUG=false.
- Pastikan storage link sudah aktif: storage -> public/storage.
- Pastikan semua cache/session/queue menggunakan Redis.
- Pastikan cron (opsional) untuk scheduler: php artisan schedule:run (via crontab).
