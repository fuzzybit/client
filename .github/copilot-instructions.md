# FuzzyBit CMS Web Client - Copilot Instructions

## Project Overview

FuzzyBit CMS is a content management system built on PHP 5.3+ with an MVC architecture. This repository contains the **private client component** that must be installed in a parent directory of `public_html/`. The system communicates with a public API and uses a dependency injection container for configuration management.

### Key Architecture Concepts

- **Two-component system**: This private "client" component works alongside a public `public_html/` component (which connects back to this client for configuration)
- **Binary Layout Engine**: The system features a unique binary layout engine that is central to the CMS architecture
- **RESTful API design**: All controllers and routes follow RESTful principles with HTTP method constants (GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD) and XHR detection
- **MVC Pattern**: Follows strict MVC separation with Controllers (action methods), Views (template rendering), and Models (business logic)
- **Dependency Injection Container**: Configuration and service management via `Container` class and `Configuration` XML-based system

## Installation & Configuration

### Initial Setup

1. Create `.htaccess` from `default.htaccess` and adjust per server requirements
2. Create `php5/global.xml` from `php5/default.global.xml` with these required settings:
   - Application `id` and `secretKey` (obtain from FuzzyBit)
   - Database credentials (`host`, `user`, `pass`, `data`)
   - Host configuration (`protocol`, `domainName`, `apiServer`)
   - Front controller defaults (`defaultRequest`, `defaultValue`, `http404`)
3. Configure the path to this client folder in the public component's `constants.php`

### XML Configuration Structure

The `php5/global.xml` controls:
- **Development/Maintenance modes**: Toggle with `<developmentOn>` and `<maintenanceOn>`
- **API Communication**: `apiServer` endpoint and credentials
- **Routing**: Default front controller request and fallback handlers
- **UI Layout**: Table grid dimensions for the layout engine

## Code Organization

### Directory Structure

```
client/                          # Private component root
├── php5/                        # Core framework files
│   ├── global.php              # Main bootstrap (includes Container & Configuration)
│   ├── Container.php           # Dependency injection container (static configuration loader)
│   ├── Configuration.php       # XML config parser (loads global.xml)
│   ├── APICaller.php           # External API communication
│   ├── logic/                  # Utility classes
│   └── default.global.xml      # Template for configuration
├── applications/
│   ├── models/
│   │   ├── front.php          # FrontController class (request parsing & routing)
│   │   ├── icontroller.php    # IController interface
│   │   └── view.php           # View class for template rendering
│   ├── controllers/
│   │   ├── front/layout.php   # MVC controller classes (mode, XOO, etc.)
│   │   └── action/            # Layout and form action handlers
│   └── views/                 # HTML templates
├── public_html/
│   ├── index.php              # Bootstrap entry point
│   └── global.php             # Deprecated bootstrap (marked for removal)
└── README.md                  # Setup and dependency documentation
```

### Bootstrap Flow

1. **public_html/index.php** requires core files from `php5/` and `applications/`
2. **Container** initializes the configuration (loads XML into static `$configuration` property)
3. **FrontController::getInstance()** routes the request based on URI
4. **Controller classes** (mode, XOO, etc.) implement `IController` interface and contain action methods
5. **View class** renders templates by injecting controller data

## Key Conventions

### Controller/Action Pattern

- Controllers are classes that implement `IController`
- Action methods are public methods within controller classes
- Request routing: `{controller}/{action}/{param1}/{param2}...`
- Controllers instantiate `FrontController` and set properties: `$frontController->body` (response), `$frontController->layout` (rendered layout)
- Example: Class `XOO` with method `action()` is called via URI `/xoo/action/params`

### HTTP Method Handling

Controllers access HTTP method constants through `FrontController`:
- `FrontController::GET`, `FrontController::POST`, `FrontController::PUT`, `FrontController::DELETE`, `FrontController::PATCH`
- `FrontController::XHR` flag indicates XMLHttpRequest
- Request data parsed from URI and form parameters

### View Rendering

- `View` class constructor takes URI and parameters
- Call `$view->render("../views/template.php")` to output HTML
- Template files receive controller data as local variables

### XML Configuration

- All configuration is stored in `php5/global.xml` (generated from template)
- Access configuration via: `Container::newConfiguration()->$property` (e.g., `->protocol`, `->domainName`, `->defaultRequest`)
- Configuration class uses DOM for XML parsing and modification

### Commenting Standards

- Use PHPDoc comments for classes: `/** @package FuzzyBit XOO */`
- Use inline comments for complex logic only
- File headers document purpose and package membership

## Testing & Quality

No automated test suite or linting configuration currently exists. Manual testing required:
- Test routing with various URIs
- Verify API communication via `APICaller` class
- Check XML configuration loading via `Container`
- Validate view rendering with different template variables

## Development Notes

- **PHP Version**: Targets PHP 5.3+ (note outdated practices; upgrade path recommended)
- **Session Handling**: Uses `$_SESSION` for token generation and form validation (see `global.php`)
- **API Integration**: `APICaller` class handles external API communication
- **Deprecation**: `public_html/global.php` marked for deprecation; use `php5/global.php` instead
- **Debug Mode**: Toggle `<developmentOn>` in XML for development output

## Related Components

- **Public Component**: Separate `public_html/` application that references this client via configuration
- **FuzzyBit API**: External REST API at configured `apiServer` endpoint
- **Layout Engine**: Binary layout system drives the view rendering and UI composition
