# Project Management System

## Overview
This project is a web-based project management system. It allows users to manage projects, tasks, and users. The system is divided into different modules for administrators and regular users.

## Folder Structure

### Root Directory
- **database.sql**: Contains the database schema for the project.
- **index.php**: The main entry point of the application.

### Directories

#### `admin/`
Contains files for administrative functionalities:
- `ajax_generate_requirements.php`: Handles AJAX requests for generating project requirements.
- `create_project.php`: Allows admins to create new projects.
- `dashboard.php`: Admin dashboard.
- `delete_project.php`: Handles project deletion.
- `edit_project.php`: Allows admins to edit project details.
- `projects.php`: Displays a list of all projects.
- `users.php`: Manages user accounts.
- `view_project.php`: Displays detailed information about a specific project.

#### `auth/`
Handles authentication-related functionalities:
- `admin_login.php`: Admin login page.
- `login.php`: User login page.
- `logout.php`: Handles user logout.
- `signup.php`: User registration page.
- `user_login.php`: Separate login page for users.

#### `config/`
Contains configuration files:
- `constants.php`: Defines application constants.
- `db.php`: Database connection file.
- `session.php`: Manages user sessions.

#### `includes/`
Contains reusable components:
- `footer.php`: Footer section of the application.
- `gemini.php`: Utility functions.
- `header.php`: Header section of the application.

#### `user/`
Contains files for user functionalities:
- `dashboard.php`: User dashboard.
- `my_projects.php`: Displays projects assigned to the user.
- `view_project.php`: Displays detailed information about a specific project for the user.

#### `assets/`
Contains static assets:
- `css/`
  - `style.css`: Stylesheet for the application.
- `js/`
  - `main.js`: JavaScript file for the application.

## Database
The `database.sql` file contains the schema for the project. Import this file into your database to set up the required tables and relationships.

## How to Run
1. Install XAMPP or any other local server environment.
2. Place the project folder in the `htdocs` directory.
3. Start Apache and MySQL from the XAMPP control panel.
4. Import the `database.sql` file into your MySQL database.
5. Open the application in your browser by navigating to `http://localhost/project_management`.

## Features
- User authentication (login, signup, logout).
- Admin and user dashboards.
- Project management (create, edit, delete, view projects).
- User management (admin only).
- Responsive design.
- Auto-Generate Requirements.

## New Feature: Auto-Generate Requirements

### Description
This feature allows administrators to automatically generate project requirements based on the project title. When creating a new project, the admin can click the "Auto Generate Requirements" button, and the system will suggest requirements based on the provided project title.

### How It Works
1. Navigate to the "Create Project" page.
2. Enter the project title in the input field.
3. Click the "Auto Generate Requirements" button.
4. The system will generate a list of suggested requirements based on the project title and display them in the requirements field.

### Files Involved
- **`admin/ajax_generate_requirements.php`**: Handles the logic for generating requirements based on the project title.
- **`admin/create_project.php`**: Contains the "Auto Generate Requirements" button and integrates the AJAX functionality.

## Technologies Used
- PHP
- MySQL
- HTML/CSS
- JavaScript

## License
This project is licensed under the MIT License.