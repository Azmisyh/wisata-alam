# Wisata Alam Indonesia - Website Rekomendasi Destinasi Wisata

## Deskripsi Proyek

Wisata Alam Indonesia adalah website rekomendasi destinasi wisata yang memungkinkan pengguna untuk menjelajahi, menemukan, dan memberikan ulasan tentang berbagai destinasi wisata di Indonesia. Website ini dikembangkan sebagai proyek UAS Web Programming dengan fitur lengkap untuk manajemen destinasi dan ulasan.

## Fitur Utama

### 🔐 **Sistem Autentikasi**
- Registrasi pengguna baru dengan auto-login
- Login/logout dengan session management
- Role-based access control (Admin & User)
- CSRF protection dan password hashing
- **User redirect ke beranda, Admin ke dashboard**

### 👥 **User Roles**
- **User**: 
  - Login langsung ke beranda (bukan dashboard)
  - Melihat destinasi wisata
  - Memberikan rating dan ulasan
  - Profil pribadi dengan statistik ulasan
  - Mencari dan filter destinasi

- **Admin**:
  - Dashboard admin dengan statistik lengkap
  - CRUD destinasi wisata
  - CRUD pengguna dengan ban/unban system
  - Profil admin dengan statistik sistem
  - Export laporan (PDF & Excel)
  - Monitoring ulasan dan aktivitas

### 🗺️ **Manajemen Destinasi**
- Kategori destinasi (Alam, Budaya, Sejarah, Rekreasi)
- Informasi lengkap (lokasi, deskripsi, gambar)
- Rating system dengan bintang 1-5
- Pencarian dan filter destinasi

### ⭐ **Sistem Review**
- Rating bintang 1-5
- Komentar/ulasan tekstual
- Statistik rating per destinasi
- Histori ulasan pengguna

### 📊 **Laporan & Export**
- Dashboard statistik real-time
- Export laporan ke Excel (.xls)
- Export laporan ke PDF
- Analisis kategori destinasi
- Tracking pengguna aktif

### 🎨 **UI/UX Features**
- Responsive design (Bootstrap 5)
- Modern interface dengan Font Awesome icons
- Smooth animations dan transitions
- Search dengan autocomplete
- Image preview dan lazy loading

## Teknologi yang Digunakan

### **Backend**
- **PHP 8.x** - Server-side programming
- **MySQL** - Database management
- **PDO** - Database abstraction layer
- **Session Management** - User authentication

### **Frontend**
- **Bootstrap 5** - CSS Framework
- **Font Awesome 6** - Icon library
- **Vanilla JavaScript** - Interactivity
- **Responsive Design** - Mobile-friendly

### **Security Features**
- Password hashing (bcrypt)
- CSRF token validation
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- Session timeout management

## Instalasi & Setup

### **Prerequisites**
- XAMPP/WAMP (PHP 8.x + MySQL)
- Web browser (Chrome, Firefox, Safari)
- Text editor (VS Code, Sublime Text)


### **Login Akun**
- **Admin**: username: `admin`, password: `admin123`
- **User**: Daftar akun baru melalui halaman registrasi

## Struktur File

```
wisataalam/
├── admin/                     # Admin panel
│   ├── manage_destinations.php
│   ├── manage_users.php
│   ├── reports.php
│   ├── get_destination.php
│   └── get_user.php
├── assets/                    # Static assets
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── uploads/                   # File uploads (jika ada)
├── config.php                 # Database & app config
├── index.php                  # Homepage
├── login.php                  # Login page
├── register.php               # Registration page
├── dashboard.php              # Admin dashboard only
├── profile.php                # User & Admin profile
├── destinations.php           # Destinations listing
├── destination.php            # Single destination detail
├── logout.php                 # Logout handler
├── database_schema.sql        # SQL database schema
└── README.md                  # This file
```

## Screenshots

*(Tambahkan screenshots project di sini)*

### Homepage
![Homepage](screenshots/homepage.png)

### Dashboard Admin
![Admin Dashboard](screenshots/admin-dashboard.png)

### Dashboard User
![User Dashboard](screenshots/user-dashboard.png)

### Destinasi Detail
![Destination Detail](screenshots/destination-detail.png)

### Laporan
![Reports](screenshots/reports.png)


### **Test Accounts**
- **Admin**: admin / admin123
- **Test User**: testuser / password123

### Link Web
wisataalam.page.gd

### Nama: Azmi Syahri Ramadhan
### NPM: 23552011068
### Kelas: TIF23CNS A

**© Copyright by 23552011068_Azmi Syahri Ramadhan_TIF 23 CNS A_UASWEB1**
