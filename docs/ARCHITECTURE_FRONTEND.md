# Frontend Architecture - QPAY

## Overview
Frontend QPAY dibangun dengan Blade templating engine, Tailwind CSS, dan DaisyUI untuk komponen UI yang konsisten dan responsif.

## Technology Stack
- **Templating**: Laravel Blade
- **Styling**: Tailwind CSS
- **UI Components**: DaisyUI
- **JavaScript**: Vanilla JS + Livewire
- **Interactive Components**: Livewire v3

## Directory Structure

```
resources/
├── css/               # Stylesheets
│   └── app.css       # Main CSS (Tailwind imports)
├── js/               # JavaScript files
│   └── app.js        # Main JS entry point
└── views/            # Blade templates
    ├── layouts/      # Layout templates
    │   ├── app.blade.php     # Main layout
    │   ├── auth.blade.php    # Auth layout
    │   └── guest.blade.php   # Guest layout
    ├── auth/         # Authentication pages
    │   ├── login.blade.php
    │   ├── register.blade.php
    │   └── forgot-password.blade.php
    ├── profile/      # Profile management
    │   └── edit.blade.php    # Profile edit form
    ├── dashboard/    # Dashboard pages
    └── components/   # Reusable components
```

## Main Layout (layouts/app.blade.php)

**Purpose**: Master layout untuk authenticated users

**Key Sections**:
1. **Header Navigation**
   - Logo/Brand
   - Search bar
   - User profile dropdown with:
     - User name and email
     - Profile link
     - Logout button

2. **Main Content Area**
   - Dynamic slot for page content
   - Max width container (max-w-7xl)

3. **Navigation Menu**
   - Dashboard
   - Products
   - Orders
   - Other app sections

**Features**:
- Responsive design
- Mobile-friendly navbar
- DaisyUI dropdown menu
- Smooth transitions

## Profile Edit Page (profile/edit.blade.php)

**Purpose**: Allow users to manage their account

**Sections**:

1. **Header**
   - Title: "My Profile"
   - Subtitle: "Manage your account information"

2. **Success/Error Alerts**
   - Display form submission status
   - Show validation errors

3. **Avatar Section**
   - DaisyUI circular avatar
   - Display user initial (first letter of name)
   - Size: w-24 h-24
   - Background: Primary color

4. **Profile Form** (form id: profile_form)
   - **Full Name**: text input
     - Placeholder: "Your full name"
     - Validation: required
   - **Email**: email input
     - Placeholder: "your@email.com"
     - Validation: required, unique
   - **Phone**: tel input
     - Placeholder: "+62 XXX XXXX XXXX"
     - Validation: optional
   - **Button**: "Save Changes" (opens confirm modal)

5. **Change Password Form** (form id: password_form)
   - **Current Password**: password input
     - Validation: required, verified
   - **New Password**: password input
     - Validation: required, strong
   - **Confirm Password**: password input
     - Validation: must match
   - **Button**: "Change Password" (opens confirm modal)

6. **Danger Zone Card** (red/error themed)
   - **Delete Account Button**: Opens delete confirmation modal
   - **Modal includes**:
     - Warning text
     - Password input for confirmation
     - Cancel button
     - Permanent delete button

**Styling**:
- Card body spacing: `space-y-8`
- Form field spacing: `space-y-6`
- Label padding: `pb-3`
- Button padding: `pt-6`
- Input text size: `text-base`
- Error messages: `pt-2`, `text-error`

**Modal Dialogs**:

1. **confirm_update_modal**
   - Title: "Confirm Changes"
   - Message: "Are you sure you want to save these changes?"
   - Buttons: Cancel, Confirm

2. **confirm_password_modal**
   - Title: "Confirm Password Change"
   - Message: "Are you sure you want to change your password?"
   - Buttons: Cancel, Confirm

3. **confirm_delete_modal**
   - Title: "Delete Account"
   - Message: "This action is permanent and cannot be undone"
   - Warning styling: bg-error/5, border-error/30
   - Password input required
   - Buttons: Cancel, Delete Account

## Form Submission Flow

1. User fills form
2. Click button (type="button") → triggers `modal.showModal()`
3. Modal appears with confirmation message
4. User clicks "Confirm" button
5. Modal submits parent form via `form="form_id"` attribute
6. Form submits to action route via POST
7. Server validates and processes
8. Redirect with success/error message

## Component Styling Standards

### Spacing System (Tailwind)
```
Card body:     space-y-8 (generous spacing)
Form fields:   space-y-6 (breathing room)
Label padding: pb-3 (separation from input)
Button space:  pt-6 (distance from field)
```

### Color Palette (DaisyUI)
```
Primary:    Primary actions (Save, Confirm)
Error:      Destructive actions (Delete, Logout)
Base:       Neutral backgrounds and text
Success:    Success messages
Warning:    Warning messages
```

### Font Sizes
```
Title:      text-5xl font-black
Subtitle:   text-lg text-base-content/70
Heading:    text-2xl font-bold
Label:      text-base font-semibold
Input:      text-base
Button:     text-base font-semibold
```

### Responsive Classes
```
Container:  max-w-7xl mx-auto px-4
Grid:       grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3
Flex:       flex flex-col md:flex-row
```

## DaisyUI Components Used

1. **Avatar**
   - Circular user initial display
   - Customizable background color
   - Class: `avatar placeholder`

2. **Modal (Dialog)**
   - Confirmation dialogs
   - HTML `<dialog>` element
   - Backdrop click to close
   - Smooth animations

3. **Alert**
   - Success/error messages
   - Icons and status indicators
   - Class: `alert alert-success` / `alert alert-error`

4. **Form Controls**
   - Input fields
   - Textarea
   - Select dropdowns
   - Validation styling

5. **Button**
   - Primary (btn-primary)
   - Ghost (btn-ghost)
   - Error (btn-error)
   - Various sizes and states

## JavaScript Interactions

### Modal Control
```javascript
// Open modal
modal.showModal();

// Close modal
modal.close();

// Auto-close on form submission
form.addEventListener('submit', () => {
    modal.close();
});
```

### Form Submission
```javascript
// Trigger form submit from modal button
<button type="submit" form="profile_form">
    Confirm
</button>
```

## Accessibility Features
- Semantic HTML elements
- ARIA labels where needed
- Keyboard navigation support
- Color contrast compliance
- Form validation feedback

## Browser Compatibility
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

## Performance Optimizations
- Minified CSS/JS in production
- Lazy loading images
- CSS purging with Tailwind
- Efficient Blade rendering
