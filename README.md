# Tasks Portal

A web portal for viewing, searching, and managing tasks fetched from the BauBuddy API.

## Project Overview

This application provides a user-friendly interface to view tasks from the BauBuddy API with features including:

- Interactive data table with sortable columns
- Real-time search functionality
- Automatic refresh of data every 60 minutes
- Local caching to improve performance
- Detailed task viewing with color-coded visual indicators
- Parent-child task relationship indicators
- Image upload capability

## Architecture

The project follows a modern PHP backend with JavaScript frontend architecture:

### Backend
- **Framework**: Slim PHP
- **Pattern**: MVC-inspired with controllers and views
- **API Communication**: PHP cURL for secure API requests with bearer token authentication

### Frontend
- **Framework**: Alpine.js for reactivity
- **CSS**: Tailwind CSS for styling
- **Storage**: LocalStorage for client-side caching

## Key Features

### Data Management
- Secure authentication with the BauBuddy API
- Automatic data refreshing every 60 minutes with countdown indicator
- Manual refresh option
- Client-side caching to reduce API calls

### User Interface
- Semi-responsive design that works on different screen sizes
- Fixed header with search functionality and user info
- Color-coded task display with left border indicating task color
- Countdown timer showing time until next automatic refresh
- Clear visual hierarchy of information

### Task Management
- Combined task ID and title display for better readability
- Parent-child relationship indicators with visual highlighting
- Detailed modal view of task information
- Organized categorization of task properties

### Data Display
- Sortable columns (Title, Parent)
- Real-time filtering as you type in the search box
- Empty state indicators when no results match search criteria
- Loading indicators during data fetching

### Image Upload
- Separate panel for image upload and preview
- File picker for selecting images from the file system
- Image preview within the modal

## Technical Implementation

### API Integration
- Secure authentication via Bearer token
- Token management and refresh handling
- Error handling for API communication failures

### State Management
- Reactive data binding with Alpine.js
- Efficient data filtering and sorting
- Persistent application state using LocalStorage

### Performance Optimizations
- Minimal DOM updates for better performance
- Client-side caching to reduce server load

## Project Structure

```
/
├── public/              # Publicly accessible files
│   ├── index.php        # Application entry point
│   ├── js/              # JavaScript files
│   │   └── tasks.js     # Alpine.js application logic
│   └── .htaccess        # Server configuration
│
├── src/                 # Application source code
│   ├── controllers/     # PHP controllers
│   │   └── TasksController.php  # API communication
│   └── views/           # PHP view templates
│       └── home.php     # Main application template
│
└── vendor/              # Dependencies (Composer)
```

## Development Setup

1. Clone the repository
2. Install dependencies with Composer: `composer install`
3. Configure a web server (Apache/Nginx) with PHP support or use the built-in PHP web server (`cd /public`; `php -S localhost:8000`)
4. Point the document root to the `/public` directory
5. Access the application through your web browser

## Technologies Used

- **PHP 8+**: Backend logic and API communication
- **Slim Framework**: Routing and middleware
- **Alpine.js**: Reactive UI components
- **Tailwind CSS**: Utility-first styling
- **LocalStorage API**: Client-side data persistence
- **Fetch API**: JavaScript API communication

## Browser Compatibility

- Chrome/Edge (Latest)
- Firefox (Latest)
- Safari (Latest)
- Mobile browsers (iOS/Android)