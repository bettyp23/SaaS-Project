# Todo Tracker SaaS

A modern, feature-rich Todo List Tracker built with Laravel 10, MySQL, and a responsive UI.

## 🚀 Quick Start

1. Clone the repository: `git clone https://github.com/bettyp23/SaaS-Project.git`
2. Copy environment file: `cp .env.example .env`
3. Install dependencies: `composer install && npm install`
4. Build assets: `npm run build`
5. Configure database in `.env` (see [Installation Guide](docs/INSTALLATION.md))
6. Start MAMP and visit `http://localhost:8888`

## 📋 Features

- ✅ **Task Management** - Create, edit, delete, and organize todos
- 📊 **Dashboard** - Overview with statistics and recent tasks
- 📌 **Kanban Board** - Drag-and-drop task organization
- 📅 **Calendar View** - Tasks with due dates
- 🏷️ **Categories** - Organize tasks with tags
- 📦 **Archive** - View and restore completed tasks
- ⚙️ **Settings** - User profile and preferences
- 🔐 **Authentication** - Secure login and registration

## 📁 Project Structure

```
SaaS-Project/
├── app/              # Application logic (Controllers, Models)
├── config/           # Configuration files
├── database/         # Migrations and seeders
├── docs/             # Documentation (see below)
├── public/           # Public assets and entry point
├── resources/        # Views, CSS, JavaScript
├── routes/           # Route definitions
├── setup/            # Database setup scripts
├── storage/          # Logs and cache files
└── tests/            # Test files
```

## 📚 Documentation

- [Installation Guide](docs/INSTALLATION.md) - Setup instructions
- [Deployment Guide](docs/DEPLOYMENT.md) - Production deployment
- [Project Summary](docs/PROJECT-SUMMARY.md) - Architecture overview
- [Technical Stack](docs/tech-stack.md) - Technologies used

## 🛠️ Technology Stack

**Backend:**
- Laravel 10
- PHP 8.1+
- MySQL (via MAMP)

**Frontend:**
- Blade Templates
- Alpine.js for interactivity
- Tailwind CSS

**Build Tools:**
- Vite
- npm/Composer

## 🔧 Configuration

### Database Setup

1. Create database in MAMP: `todo_tracker_saas`
2. Import schema: `mysql -u vibe_templates -p todo_tracker_saas < setup/database-schema.sql`
3. Update `.env` with your database credentials

### Environment Variables

```
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=todo_tracker_saas
DB_USERNAME=vibe_templates
DB_PASSWORD=vibe_templates_password
APP_URL=http://localhost:8888
```

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Test database connection
php setup/test-db.php
```

## 📊 Application Capabilities

- **Multi-user support** with authentication
- **CRUD operations** for todos with full validation
- **Real-time statistics** on dashboard
- **Drag-and-drop** Kanban board
- **Calendar integration** for due dates
- **Tag-based organization**
- **Archive and restore** functionality
- **User settings** and profile management

## 🔒 Security

- CSRF protection
- Authentication middleware
- Authorization checks
- Password hashing
- Session security

## 📈 Roadmap

- [ ] Team collaboration features
- [ ] Subscription billing
- [ ] Advanced filtering
- [ ] Export/Import functionality
- [ ] Mobile app support

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙋 Support

For issues and questions, please open an issue on GitHub.

---

**Built with ❤️ using Laravel**
