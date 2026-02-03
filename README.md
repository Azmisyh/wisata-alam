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

## Struktur Database

```sql
-- Users Table
users (id, username, password, email, full_name, role, created_at, updated_at)

-- Destinations Table  
destinations (id, name, description, location, province, category, image_url, rating_avg, review_count, created_at, updated_at)

-- Reviews Table
reviews (id, destination_id, user_id, rating, comment, created_at, updated_at)

-- Sessions Table
sessions (id, user_id, ip_address, user_agent, payload, last_activity)
```

## Instalasi & Setup

### **Prerequisites**
- XAMPP/WAMP (PHP 8.x + MySQL)
- Web browser (Chrome, Firefox, Safari)
- Text editor (VS Code, Sublime Text)

### **Langkah Instalasi**

1. **Clone/Download project**
   ```bash
   git clone [repository-url]
   cd wisataalam
   ```

2. **Database Setup**
   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Buat database baru bernama `wisata_alam`
   - Import file `database_schema.sql`

3. **Konfigurasi**
   - Edit file `config.php` sesuai kebutuhan:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'wisata_alam');
   ```

4. **Start Server**
   - Start Apache dan MySQL melalui XAMPP Control Panel
   - Akses website melalui browser: `http://localhost/wisataalam/`

5. **Login Akun**
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

## Video Demo

*(Link video demo project)*
[Watch Project Demo](https://youtube.com/watch?v=your-video-id)

## API Endpoints

### **Public API**
- `GET /` - Homepage dengan destinasi populer
- `GET /destinations.php` - Listing destinasi dengan filter
- `GET /destination.php?id={id}` - Detail destinasi

### **Authenticated API**
- `POST /login.php` - User login
- `POST /register.php` - User registration
- `POST /destination.php` - Add review (POST method)

### **Admin API**
- `GET /admin/` - Admin dashboard
- `POST /admin/manage_destinations.php` - CRUD destinations
- `GET /admin/get_destination.php?id={id}` - Get destination data
- `GET /admin/reports.php?export=excel` - Export Excel
- `GET /admin/reports.php?export=pdf` - Export PDF

## Testing

### **Manual Testing Checklist**
- [ ] User registration works correctly
- [ ] Login/logout functionality
- [ ] Admin can CRUD destinations
- [ ] User can add reviews
- [ ] Search and filter destinations
- [ ] Export reports (Excel/PDF)
- [ ] Responsive design on mobile
- [ ] CSRF protection works
- [ ] SQL injection prevention

### **Test Accounts**
- **Admin**: admin / admin123
- **Test User**: testuser / password123

## Deployment

### **Local Development**
```bash
# Start XAMPP
# Place project in htdocs/
# Access via http://localhost/wisataalam/
```

### **Production Deployment**
1. Upload files to web server
2. Create MySQL database
3. Import database schema
4. Update config.php with production credentials
5. Set proper file permissions (755 for directories, 644 for files)

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## Known Issues & Solutions

### **Common Issues**
1. **Database Connection Error**
   - Check MySQL service is running
   - Verify database credentials in config.php
   - Ensure database exists

2. **Session Issues**
   - Check session.save_path in php.ini
   - Verify folder permissions
   - Clear browser cookies

3. **Image Upload Issues**
   - Check upload folder permissions
   - Verify file size limits
   - Ensure GD library is enabled

## Future Enhancements

- [ ] Google Maps integration
- [ ] Image upload compression
- [ ] Email notification system
- [ ] Social media login
- [ ] Multi-language support
- [ ] Mobile app development
- [ ] Advanced analytics dashboard
- [ ] API for third-party integration

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

- **Developer**: [Your Name]
- **Email**: [your-email@example.com]
- **Phone**: +62 812-3456-7890
- **GitHub**: [github-username]

---

**© Copyright by 23552011068_Azmi Syahri Ramadhan_TIF 23 CNS A_UASWEB1**
