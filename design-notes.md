# Todo List Tracker - Design Notes

## Design System Overview
- **Framework**: Bootstrap 5.3.3 with custom CSS overrides
- **Design Philosophy**: Clean, modern, and intuitive user interface
- **Color Scheme**: Bootstrap's default color palette with custom accent colors
- **Typography**: Bootstrap's typography system with custom font weights
- **Responsive**: Mobile-first approach with breakpoint-specific layouts

## Core Application Layout

### Main Navigation
- **Navbar**: Bootstrap's fixed-top navbar with brand logo and user menu
- **Sidebar**: Collapsible sidebar for main navigation (desktop) / offcanvas (mobile)
- **Breadcrumbs**: Bootstrap breadcrumb component for navigation context
- **User Profile**: Dropdown menu with user avatar and settings

### Dashboard Layout
```html
<!-- Main Dashboard Structure -->
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar Navigation -->
    <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
      <!-- Navigation items -->
    </nav>
    
    <!-- Main Content Area -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <!-- Dashboard content -->
    </main>
  </div>
</div>
```

## Dashboard Design

### Dashboard Overview Cards
- **Stats Cards**: Bootstrap card components with icons and metrics
  - Total Todos
  - Completed Today
  - Overdue Items
  - This Week's Progress
- **Quick Actions**: Button group with primary actions
- **Recent Activity**: Timeline component with recent todo updates
- **Progress Charts**: Simple progress bars and charts

### Dashboard Components
```html
<!-- Stats Cards Example -->
<div class="row">
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
              Total Todos
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">24</div>
          </div>
          <div class="col-auto">
            <i class="fas fa-calendar fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

## Kanban Board Design

### Board Layout
- **Columns**: Bootstrap grid system with equal-width columns
- **Cards**: Bootstrap card components for todo items
- **Drag & Drop**: HTMX integration for column updates
- **Column Headers**: Bootstrap badge components for counts

### Kanban Components
```html
<!-- Kanban Board Structure -->
<div class="row">
  <div class="col-md-3">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">To Do</h6>
        <span class="badge bg-primary">3</span>
      </div>
      <div class="card-body">
        <!-- Todo cards -->
        <div class="card mb-3" draggable="true">
          <div class="card-body">
            <h6 class="card-title">Task Title</h6>
            <p class="card-text small text-muted">Description</p>
            <div class="d-flex justify-content-between">
              <small class="text-muted">Due: Dec 15</small>
              <span class="badge bg-warning">High</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

### Kanban Card States
- **To Do**: Default state with primary styling
- **In Progress**: Warning/amber styling
- **Review**: Info/blue styling
- **Done**: Success/green styling
- **Overdue**: Danger/red styling

## Calendar Design

### Calendar Layout
- **Month View**: Bootstrap table with calendar grid
- **Week View**: Horizontal timeline layout
- **Day View**: Vertical timeline with time slots
- **Event Cards**: Bootstrap cards with color coding

### Calendar Components
```html
<!-- Calendar Month View -->
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">December 2024</h5>
      <div class="btn-group" role="group">
        <button class="btn btn-outline-secondary btn-sm">Today</button>
        <button class="btn btn-outline-secondary btn-sm">Month</button>
        <button class="btn btn-outline-secondary btn-sm">Week</button>
      </div>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th class="text-center">Sun</th>
            <th class="text-center">Mon</th>
            <!-- ... other days ... -->
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="text-center">
              <div class="date-cell">
                <span class="date-number">1</span>
                <div class="events">
                  <span class="badge bg-primary small">Meeting</span>
                </div>
              </div>
            </td>
            <!-- ... other cells ... -->
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
```

## Todo List Components

### Todo Item Design
- **Checkbox**: Bootstrap form-check for completion status
- **Priority Indicators**: Bootstrap badges with color coding
- **Due Dates**: Bootstrap badge components
- **Tags**: Bootstrap badge components with different colors
- **Actions**: Bootstrap button group with edit/delete actions

### Todo Item Layout
```html
<!-- Todo Item Component -->
<div class="card mb-2">
  <div class="card-body">
    <div class="d-flex align-items-start">
      <div class="form-check me-3">
        <input class="form-check-input" type="checkbox" id="todo1">
        <label class="form-check-label" for="todo1"></label>
      </div>
      <div class="flex-grow-1">
        <h6 class="card-title mb-1">Complete project proposal</h6>
        <p class="card-text small text-muted">Write the initial draft and review with team</p>
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex gap-2">
            <span class="badge bg-danger">High Priority</span>
            <span class="badge bg-info">Work</span>
            <span class="badge bg-secondary">Due: Dec 20</span>
          </div>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary btn-sm">Edit</button>
            <button class="btn btn-outline-danger btn-sm">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

## Form Design

### Todo Creation Form
- **Modal**: Bootstrap modal for quick todo creation
- **Form Fields**: Bootstrap form components with validation
- **Date Picker**: Bootstrap date input with custom styling
- **Priority Selector**: Bootstrap select with custom options
- **Tag Input**: Bootstrap input group with tag chips

### Form Components
```html
<!-- Todo Creation Modal -->
<div class="modal fade" id="createTodoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="todoTitle" class="form-label">Title</label>
            <input type="text" class="form-control" id="todoTitle" required>
          </div>
          <div class="mb-3">
            <label for="todoDescription" class="form-label">Description</label>
            <textarea class="form-control" id="todoDescription" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="todoPriority" class="form-label">Priority</label>
                <select class="form-select" id="todoPriority">
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="todoDueDate" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="todoDueDate">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="todoTags" class="form-label">Tags</label>
            <input type="text" class="form-control" id="todoTags" placeholder="Enter tags separated by commas">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Create Todo</button>
      </div>
    </div>
  </div>
</div>
```

## User Interface Elements

### Buttons and Actions
- **Primary Actions**: Bootstrap btn-primary for main actions
- **Secondary Actions**: Bootstrap btn-outline-secondary for secondary actions
- **Danger Actions**: Bootstrap btn-danger for destructive actions
- **Icon Buttons**: Bootstrap icons with button styling
- **Floating Action Button**: Fixed position button for quick todo creation

### Navigation Elements
- **Sidebar Navigation**: Bootstrap list-group for navigation items
- **Breadcrumbs**: Bootstrap breadcrumb component
- **Pagination**: Bootstrap pagination for large lists
- **Tabs**: Bootstrap nav-tabs for different views

### Feedback Components
- **Alerts**: Bootstrap alert component for notifications
- **Toast Notifications**: Bootstrap toast for success/error messages
- **Progress Bars**: Bootstrap progress component for completion tracking
- **Loading States**: Bootstrap spinner for async operations

## Responsive Design

### Mobile Layout
- **Collapsible Sidebar**: Bootstrap offcanvas for mobile navigation
- **Stacked Cards**: Vertical card layout on mobile
- **Touch-Friendly**: Larger touch targets for mobile interaction
- **Swipe Gestures**: Alpine.js integration for mobile gestures

### Tablet Layout
- **Two-Column Layout**: Sidebar + main content
- **Responsive Grid**: Bootstrap grid system for tablet screens
- **Touch Navigation**: Optimized for tablet interaction

### Desktop Layout
- **Three-Column Layout**: Sidebar + main content + details panel
- **Hover States**: Enhanced hover effects for desktop
- **Keyboard Navigation**: Full keyboard accessibility

## Color Scheme

### Primary Colors
- **Primary**: Bootstrap's primary blue (#0d6efd)
- **Secondary**: Bootstrap's secondary gray (#6c757d)
- **Success**: Bootstrap's success green (#198754)
- **Warning**: Bootstrap's warning yellow (#ffc107)
- **Danger**: Bootstrap's danger red (#dc3545)
- **Info**: Bootstrap's info cyan (#0dcaf0)

### Custom Accent Colors
- **Todo Priority High**: #dc3545 (red)
- **Todo Priority Medium**: #ffc107 (yellow)
- **Todo Priority Low**: #198754 (green)
- **Overdue**: #dc3545 (red)
- **Completed**: #198754 (green)
- **In Progress**: #0dcaf0 (cyan)

## Typography

### Font Hierarchy
- **Headings**: Bootstrap's heading classes (h1-h6)
- **Body Text**: Bootstrap's default font stack
- **Code**: Bootstrap's code styling
- **Small Text**: Bootstrap's small text utility

### Text Utilities
- **Text Colors**: Bootstrap's text color utilities
- **Text Alignment**: Bootstrap's text alignment utilities
- **Font Weight**: Bootstrap's font weight utilities
- **Line Height**: Bootstrap's line height utilities

## Animation and Transitions

### Bootstrap Transitions
- **Fade**: Bootstrap's fade transition for modals
- **Slide**: Bootstrap's slide transition for dropdowns
- **Collapse**: Bootstrap's collapse transition for accordions

### Custom Animations
- **Todo Completion**: CSS transition for checkbox states
- **Drag and Drop**: Smooth transitions for kanban cards
- **Loading States**: Spinner animations for async operations
- **Hover Effects**: Subtle hover animations for interactive elements

## Accessibility Features

### Bootstrap Accessibility
- **ARIA Labels**: Proper ARIA labeling for screen readers
- **Focus Management**: Keyboard navigation support
- **Color Contrast**: Bootstrap's accessible color combinations
- **Screen Reader Support**: Semantic HTML structure

### Custom Accessibility
- **High Contrast Mode**: Alternative color schemes
- **Keyboard Shortcuts**: Custom keyboard shortcuts for power users
- **Focus Indicators**: Clear focus indicators for navigation
- **Alternative Text**: Proper alt text for images and icons

This design system provides a comprehensive foundation for building a modern, accessible, and user-friendly Todo List Tracker application using Bootstrap 5.3 components and design patterns.
