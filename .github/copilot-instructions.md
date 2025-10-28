# General AI Agent Instructions for Laravel Applications

## CRITICAL OPERATIONAL DIRECTIVE

**ANALYSIS-ONLY MODE BY DEFAULT**: Unless the user explicitly requests implementation or file changes, ONLY provide analysis, suggestions, and recommendations. NEVER modify files, create new files, or implement changes without explicit user instruction.

**READING BLADE AND HTML FILES**: When analyzing Blade or HTML files, focus on understanding the structure, data bindings, and user interactions. Identify how data flows from controllers to views and note any dynamic elements that may require special handling. Find the head section, if there isn't one, find the component present that has it. Find any external css files and js files. Never do any inline styling, use the external file for all styling without exception.

## General Laravel Application Guidelines

### Framework Configuration
```
Laravel Version: 12.x or latest
PHP Version: ^8.2 or compatible
Database: SQLite (development), MySQL/PostgreSQL (production)
Authentication: Laravel Sanctum or Laravel Breeze
Frontend: Blade templates, custom CSS/JS
Testing: PHPUnit
Development Tools: Laravel Telescope, Pulse, Tinker, Pint
```

### Key Dependencies
```json
{
  "production": [
    "laravel/framework",
    "laravel/sanctum"
  ],
  "development": [
    "laravel/telescope",
    "laravel/breeze",
    "laravel/sail"
  ]
}
```

### Core User Management (Example)
```sql
-- users table
- id (bigint, PK)
- name (varchar 255)
- email (varchar 255, unique)
- password (varchar 255, hashed)
- role (enum: admin|editor|regular)
- email_verified_at, remember_token, timestamps
```

### Application Structure

#### Model Architecture
- Use Eloquent ORM for model relationships
- Implement role-based authorization logic
- Use fillable attributes and casts for data integrity

#### Controller Architecture
- Use resource controllers for CRUD operations
- Implement validation and error handling
- Use policies for authorization

#### View Architecture
- Organize views by user role and feature
- Use Blade components for reusable UI
- Follow Laravel conventions for layouts and partials

### Middleware & Security
- Use built-in CSRF protection
- Validate all user input
- Use Eloquent ORM to prevent SQL injection
- Implement role-based authorization using policies
- Hash passwords securely
- Use API authentication (Sanctum/Breeze)

### Route Architecture
- Group routes by access level (public, authenticated, admin)
- Use RESTful resource routes for CRUD
- Apply middleware for role-based protection

### Development Patterns & Conventions
- Use strict typing and PSR-4 autoloading
- Type hint all methods
- Leverage Laravel features (validation, dependency injection)
- Use factories for testing
- Enable timestamps on models
- Use JSON casting for complex data
 - Use the global CSS variables defined in `public/css/aurora-general.css` for all styling (colors, typography, spacing, radii, shadows, transitions, z-index). This keeps visual consistency across components and views.

### File Upload Patterns
- Store files in `public/storage` with symbolic links
- Validate file types and sizes
- Clean up files on record deletion

### Testing Infrastructure
- Organize tests into Feature and Unit directories
- Use RefreshDatabase trait for clean test environment
- Use factories for model creation
- Test authentication and authorization logic

### Deployment & Configuration
- Use environment variables for configuration
- Compile assets with Vite or Laravel Mix
- Store images in storage/app/public/

## AI Agent Operational Guidelines

### Analysis Protocol
1. **Request Classification**: Determine intent (debugging, feature, optimization, etc.)
2. **Context Gathering**: Identify relevant models, controllers, views
3. **Relationship Mapping**: Understand data dependencies
4. **Security Assessment**: Check authorization and validation requirements
5. **Suggestion Formation**: Provide specific, actionable recommendations

### Response Framework
```
ANALYSIS ONLY (unless explicitly requested otherwise):
1. Identify the specific Laravel components involved
2. Reference relevant models, relationships, and migrations
3. Consider role-based access control implications  
4. Suggest implementation approach with code examples
5. Highlight potential security, performance, or architectural concerns
6. Provide testing recommendations
7. Suggest deployment considerations if applicable
```

### Code Suggestion Guidelines
- Always include proper type hints and return types
- Use Laravel's built-in features (validation, authorization, etc.)
- Follow the existing project patterns
- Consider the role-based access control system
- Include error handling and user feedback
- Suggest appropriate tests
- Consider performance implications

### Security Checklist for Suggestions
- [ ] Input validation implemented
- [ ] Authorization checks included  
- [ ] CSRF protection considered
- [ ] SQL injection prevention verified
- [ ] XSS prevention implemented
- [ ] File upload security addressed
- [ ] Role-based access enforced

## Common Development Scenarios

### Adding New Features
1. Create/modify models with proper relationships
2. Add validation rules and authorization policies
3. Implement controller logic with proper error handling
4. Create/update views with consistent styling
5. Add appropriate routes with middleware
6. Include comprehensive tests
7. Update documentation

### Debugging Issues
1. Check Laravel logs in storage/logs/
2. Use Telescope for request tracing (if enabled)
3. Verify database relationships and constraints
4. Check middleware and authorization logic
5. Validate input and output data flow
6. Review error handling and user feedback

### Performance Optimization
1. Identify N+1 query problems
2. Implement eager loading where appropriate
3. Add database indexes for frequently queried columns
4. Consider caching for settings and static data
5. Optimize image storage and serving
6. Review and optimize asset compilation

## EXECUTION MANDATE

When responding to user queries:

1. **ANALYZE FIRST**: Always examine the request in context of the project structure
2. **SUGGEST ONLY**: Provide detailed implementation suggestions without making changes
3. **REFERENCE SPECIFICALLY**: Mention exact files, models, and relationships involved
4. **CONSIDER SECURITY**: Always include authorization and validation requirements
5. **PROVIDE CONTEXT**: Explain how suggestions fit into the larger application architecture
6. **INCLUDE TESTING**: Suggest appropriate test coverage for any proposed changes

**REMEMBER**: Default mode is ANALYSIS and SUGGESTIONS only. Implement changes only when explicitly requested by the user.

## Database Schemas & Fields

Document all database tables, fields, and relationships here as you add them to the project. Update this section whenever a new table or field is introduced.

### Clients Database Schema
```
Table: clients
- id (bigint, PK)
- client (string)
- phone (string)
- address (string)
- profile_url (string, nullable)
- pet_name (string)
- pet_species (string)
- gender (string)
- pet_birthday (date)
- pet_breed (string)
- pet_weight (string)
- pet_color (json or array of strings)   # formerly color
- living_space (string)
- pet_characteristics (json or array of strings) # formerly inspiration
- plant_test (string)
- plant (string)      
- plant_description (string)
- created_at (timestamp)
```


### Tests Database Schema
```
Table: tests
- id (bigint, PK)
- email (string)
- - client (string)
- - email (string)
- - pet_name (string, nullable)
- - pet_species (string)
- - gender (string)
- - pet_birthday (date)
- - pet_breed (string)
- - pet_weight (string)
- - pet_color (json or array of strings)   # formerly color
- - living_space (string)
- - pet_characteristics (json or array of strings) # formerly inspiration
- - plant_test (string)
- - plant (string)      
- - plant_description (string)

When adding these tables, update the Model, Controller, and Feature tests accordingly. Ensure use of `RefreshDatabase` in tests and cast `metadata` as `array` in the model.