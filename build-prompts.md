# SaaS Todo List Tracker - Build Prompts

## **Prompt 1: Project Foundation & Setup**
```
"Set up a modern Laravel 10+ SaaS application for a todo list tracker with the following requirements:
- Create a new Laravel project with PHP 8.1+
- Configure MySQL database connection
- Set up Redis for caching and sessions
- Install and configure Laravel Sanctum for API authentication
- Set up Laravel Breeze for basic authentication scaffolding
- Configure environment variables for database, Redis, and mail
- Create basic project structure with organized directories for models, controllers, services, and resources
- Set up basic routing structure for API and web routes
- Configure CORS for frontend integration
- Include basic security middleware and rate limiting"
```

## **Prompt 2: Database Schema & Models**
```
"Design and implement the complete database schema for the todo tracker SaaS application:
- Create migrations for users, todos, lists, tags, teams, team_members, notifications tables
- Implement proper relationships between all entities
- Add indexes for performance optimization
- Create Eloquent models with relationships and accessors/mutators
- Include soft deletes for todos and lists
- Add timestamps and proper foreign key constraints
- Create seeders for initial data (admin user, sample todos)
- Implement model factories for testing
- Add database validation rules and constraints
- Include audit trails for important changes"
```

## **Prompt 3: User Authentication & Management System**
```
"Build a comprehensive user authentication system with:
- User registration with email verification
- Secure login with 'remember me' functionality
- Password reset via email
- Account lockout after failed attempts (5 attempts, 15-minute lockout)
- User profile management (name, email, timezone, preferences)
- Profile picture upload with image optimization
- Social login integration (Google, GitHub, Microsoft)
- Two-factor authentication (2FA) support
- Account deletion with data export option
- Implement proper password hashing and security measures
- Add user roles and permissions system"
```

## **Prompt 4: Core Todo Management API**
```
"Create the core todo management API with full CRUD operations:
- Todo creation with title, description, due dates, priority levels
- Todo organization with custom lists/projects
- Drag-and-drop reordering functionality
- Bulk operations (mark complete, delete, move, assign tags)
- Search and filter todos by various criteria
- Sort todos by due date, priority, creation date
- Todo status management (complete/incomplete, archive, restore)
- File attachments for todos
- Comments/notes on todos
- Time tracking for todos
- Implement proper API validation and error handling
- Add API rate limiting and authentication middleware"
```

## **Prompt 5: Responsive Frontend Interface**
```
"Build a modern, responsive frontend using React with TypeScript:
- Create a clean, intuitive todo management interface
- Implement drag-and-drop functionality for todo reordering
- Add real-time updates using WebSocket connections
- Build responsive design that works on mobile, tablet, and desktop
- Implement dark/light theme support
- Add keyboard shortcuts for power users
- Create loading states and error handling
- Build reusable components for todos, lists, and forms
- Implement client-side routing with React Router
- Add Progressive Web App (PWA) capabilities
- Include offline functionality for basic operations"
```

## **Prompt 6: Advanced Todo Features**
```
"Implement advanced todo management features:
- Recurring todos (daily, weekly, monthly, custom intervals)
- Subtasks for complex todos with progress tracking
- File attachments with image preview and document support
- Advanced search with filters (date range, priority, tags, status)
- Bulk operations interface for managing multiple todos
- Todo templates for frequently created items
- Export functionality (CSV, JSON, PDF)
- Import from other todo apps (Todoist, Asana, Trello)
- Advanced sorting and filtering options
- Todo analytics and completion statistics"
```

## **Prompt 7: Team Collaboration & Sharing**
```
"Build team collaboration and sharing features:
- Create and manage teams with role-based permissions
- Invite team members via email with permission levels
- Share individual todos with other users
- Share entire lists/projects with different access levels
- Team activity feed showing all member actions
- Real-time collaboration on shared todos
- Comment system for team communication
- Notification system for team activities
- Public todo lists option
- Team analytics and productivity metrics
- Implement proper permission checks for all shared content"
```

## **Prompt 8: Notification & Reminder System**
```
"Implement comprehensive notification and reminder system:
- Email notifications for due date reminders (configurable timing)
- Daily/weekly summary emails with todo statistics
- In-app real-time notifications for shared todos and team activities
- Overdue todo alerts with escalation
- Team activity notifications
- System announcements and updates
- Push notifications for mobile users
- Email template system with customizable designs
- Notification preferences and settings
- Batch notification processing for performance
- Integration with email services (SendGrid, Mailgun)"
```

## **Prompt 9: Subscription & Billing System**
```
"Implement subscription and billing management:
- Create subscription tiers (Free, Pro, Enterprise)
- Integrate Stripe for payment processing
- Build subscription management dashboard
- Implement usage tracking and limits
- Add subscription upgrade/downgrade functionality
- Create billing history and invoice generation
- Implement trial periods and promotional codes
- Add subscription cancellation and data retention policies
- Build admin panel for subscription management
- Implement webhook handling for payment events
- Add subscription analytics and reporting"
```

## **Prompt 10: Security, Performance & Deployment**
```
"Implement security, performance optimization, and deployment:
- Add comprehensive security measures (CSRF, XSS protection, SQL injection prevention)
- Implement data encryption at rest and in transit
- Add GDPR compliance features (data export, deletion, consent management)
- Optimize database queries and add proper indexing
- Implement caching strategies for improved performance
- Add monitoring and logging for error tracking
- Set up automated backups and disaster recovery
- Configure CDN for static assets
- Implement API rate limiting and abuse prevention
- Add health checks and monitoring endpoints
- Set up CI/CD pipeline for automated deployment
- Configure production environment with load balancing and auto-scaling"
```

---

**Note:** These prompts are designed to build the application systematically, starting with the foundation and core features, then moving to advanced functionality, collaboration features, and finally security and deployment considerations. Each prompt is comprehensive enough to guide the development of major features while maintaining focus on specific functionality areas.
