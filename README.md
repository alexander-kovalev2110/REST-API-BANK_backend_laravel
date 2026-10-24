# Backend Architecture (Laravel Implementation)

* **[Project description](https://github.com/alexander-kovalev2110/full-stack-web-proj_REST-API-BANK/blob/master/PHP-test.pdf)**
* **[Swagger (OpenAPI)](https://alexander-kovalev2110.github.io/full-stack-web-proj_REST-API-BANK/Swagger-OpenAPI/dist/index.html)**

This project is built with **PHP 8.2+ + Laravel + Eloquent ORM** and follows the principles of **Hexagonal Architecture (Ports and Adapters)** and **Domain-Driven Design (DDD)**. It features a strict separation between HTTP presentation, application, and domain logic using **CQRS** (Command Query Responsibility Segregation).

The architecture ensures predictable data flow:

**Command (Write) Flow:**
```
FE → HTTP Request → JWT Middleware → Controller → CommandBus → CommandHandler → Domain Entities → Repository Port → Eloquent Adapter → JsonResponse → FE
```

**Query (Read) Flow:**
```
FE → HTTP Request → JWT Middleware → Controller → QueryBus → QueryHandler → Query Port → Eloquent Adapter → View DTO → JsonResponse → FE
```

---

## Architectural Principles

*   **Hexagonal Architecture (Ports & Adapters)** — The core domain and application layers are isolated from external technical dependencies. They communicate with the outside world using abstract interfaces ("Ports"). Frameworks, databases, security, and external APIs are treated as external "Adapters" located strictly in the Infrastructure layer.
*   **Domain-Driven Design (DDD)** — The codebase is structured around business contexts and subdomains (`Customer` and `Transaction`). Domain aggregates and entities protect business invariants, while repository interfaces abstract data persistence.
*   **Separation of Concerns** — Each layer has a single responsibility.
*   **Thin Controllers / Fat Domain & Handlers** — Controllers only delegate to command/query buses; business rules are enforced inside domain models, and orchestration lives in handler classes.
*   **DTO-driven boundaries** — Request and response models are explicit and decoupled from domain objects.
*   **Domain-first approach** — Application handlers operate on domain entities and interfaces, abstracting database infrastructure.
*   **Explicit CQRS data flow** — Clear separation of write actions (Commands) and read actions (Queries) with no side effects.

---

## Request Lifecycle Overview

### 1. Kernel Layer (HTTP Entry Point)
*   **Purpose:** Handles the incoming HTTP request and bootstraps the Laravel application.
*   **Flow:** `Incoming HTTP Request → bootstrap/app.php → Routing`

### 2. Security Layer
*   **Purpose:** Handles authentication and authorization.
*   **Responsibilities:**
    *   JWT authentication (resolving endpoints via tokens).
    *   Checking user permissions.
    *   Resolving the authenticated `Customer` entity.
    *   Flow: `FE → Routing → JWTAuthMiddleware → Controller`

### 3. Input Validation & Middleware Layer
*   **Purpose:** Validates HTTP request data (body and query parameters) and authenticated user attributes.
*   **Key Components Used:**
    *   `Laravel Validator` — validates JSON request bodies with explicit error messages.
    *   `Query parameters handling` — parses and validates GET query parameters into search/filter criteria.
    *   `JWTAuthMiddleware` — extracts and validates the Bearer token, setting the authenticated `Customer`.

### 4. DTO Layer (Request Models)
*   **Purpose:** Strongly typed input definitions protecting application boundaries.
*   **Structure:**
    *   [app/Application/Customer/DTO/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Application/Customer/DTO) (e.g. `RegisterRequest`, `LoginRequest`)
    *   [app/Application/Transaction/DTO/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Application/Transaction/DTO) (e.g. `AmountTransactionRequest`, `FilterTransactionRequest`)
*   **Characteristics:**
    *   Strict typing and readonly attributes.

### 5. Controller Layer (HTTP Orchestration)
*   **Purpose:** Coordinates request handling without containing business or persistence logic.
*   **Characteristics:**
    *   No business rules.
    *   No direct database queries.
    *   Dispatches commands or queries to the bus.
*   **Structure:**
    *   [app/Api/Controller/Customer/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Api/Controller/Customer) (e.g. `RegisterController`, `LoginController`)
    *   [app/Api/Controller/Transaction/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Api/Controller/Transaction) (e.g. `CreateTransactionController`, `GetTransactionListController`)

### 6. Command & Query Bus
*   **Purpose:** Decouples controllers from handlers.
*   **Structure:**
    *   [app/Infrastructure/Bus/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Infrastructure/Bus) (e.g. `CommandBus` and `QueryBus`)

### 7. Handler Layer (Application Logic)
*   **Purpose:** Implements application-level orchestration and transactional boundaries.
*   **Responsibilities:**
    *   Retrieve domain entities from repository ports.
    *   Trigger state modifications on domain aggregates.
    *   Save changed entities back to persistent storage.
*   **Structure:**
    *   [app/Application/Customer/Command/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Application/Customer/Command) (e.g. `RegisterCustomerHandler`, `LoginCustomerHandler`)
    *   [app/Application/Transaction/Command/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Application/Transaction/Command) (e.g. `CreateTransactionHandler`, `UpdateTransactionHandler`, `DeleteTransactionHandler`)
    *   [app/Application/Transaction/Query/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Application/Transaction/Query) (e.g. `GetTransactionListHandler`, `GetTransactionHandler`)

### 8. Repository Layer (Data Access Interfaces)
*   **Purpose:** Encapsulate database retrieval and persistence.
*   **Structure:**
    *   Domain interfaces (Ports): [app/Domain/Customer/CustomerRepositoryInterface.php](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Domain/Customer/CustomerRepositoryInterface.php) and [app/Domain/Transaction/TransactionRepositoryInterface.php](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Domain/Transaction/TransactionRepositoryInterface.php).
    *   Infrastructure implementations (Adapters): [app/Infrastructure/Repository/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Infrastructure/Repository) and [app/Infrastructure/Query/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Infrastructure/Query).

### 9. Domain Layer (Entities & Exceptions)
*   **Purpose:** Represent core business models, rules, and validation invariants.
*   **Structure:**
    *   [app/Domain/Customer/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Domain/Customer)
    *   [app/Domain/Transaction/](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Domain/Transaction)
*   **Characteristics:**
    *   Free from HTTP concerns.
    *   Contains database mapping annotations (Eloquent).

### 10. Response Layer (Output Models / View DTOs)
*   **Purpose:** Defines structured, immutable payloads returned by the API.
*   **Examples:**
    *   [AuthView.php](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Application/Customer/DTO/AuthView.php)
    *   [TransactionView.php](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Application/Transaction/DTO/TransactionView.php)
*   **Benefits:**
    *   Prevents exposing internal ORM entity relations directly to JSON.
    *   Decouples API contract representation from the database schema.

---

## Exception Handling & Validation

1.  **Request Payload & Query Validation:**
    Validation violations are formatted into a structured `400 Bad Request` JSON response returning only the first error string: `{"error": "<message>"}`.
2.  **Domain Exception Handling:**
    Domain-level exceptions (e.g. `CustomerAlreadyExistsException`, `CustomerNotFoundException`, `InvalidCredentialsException`, `TransactionNotFoundException`) extend [DomainException](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/app/Domain/Exception/DomainException.php) which defines the target HTTP status. They are globally intercepted by [bootstrap/app.php](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/bootstrap/app.php) and converted to consistent `{"error": "<message>"}` JSON responses.
3.  **Global Fallback & System Errors:**
    HTTP exceptions (404, 405) and generic unhandled exceptions (500) are also intercepted by [bootstrap/app.php](file:///d:/KA/tests/github/REST-API-BANK_backend_laravel/bootstrap/app.php) returning `{"error": "<message>"}` or `{"error": "Internal Server Error"}` to strictly maintain the Frontend contract.

---

## Folder Structure Overview

```
app/
  Api/                      # Primary (Driving) Adapters
    Controller/             # HTTP Entry Points (Customer, Transaction)
  Domain/                   # Core business logic & entities
    Customer/               # Customer domain entity, interfaces, exceptions
    Transaction/            # Transaction domain entity, interfaces, exceptions
    Exception/              # Base domain exceptions
  Application/              # Application orchestration & Use cases
    Customer/               # Customer Commands, Handlers, DTOs, Ports
    Transaction/            # Transaction Commands, Queries, Handlers, DTOs, Ports
  Infrastructure/           # Secondary (Driven) Adapters
    Bus/                    # CQRS CommandBus & QueryBus implementations
    Middleware/             # HTTP Middlewares (JWTAuthMiddleware)
    Query/                  # Optimized read projections (Eloquent)
    Repository/             # Eloquent database mapping repositories
    Security/               # Password hashing & JWT Token generation adapters
bootstrap/
  app.php                   # Application configuration, routing & global exception handling
config/
  cors.php                  # Cross-Origin Resource Sharing (CORS) configuration
routes/
  api.php                   # API route definitions
```

---

## Running Tests

The test suite is built using **PHPUnit** and covers both Customer and Transaction logic.

### Commands to Run Tests

#### On Linux / macOS / Git Bash:
*   Run all tests:
    ```bash
    ./vendor/bin/phpunit
    ```
*   Run a specific test class:
    ```bash
    ./vendor/bin/phpunit tests/Unit/CustomerServiceTest.php
    ```

#### On Windows (Command Prompt - CMD):
*   Run all tests:
    ```cmd
    vendor\bin\phpunit
    ```
*   Run a specific test class:
    ```cmd
    vendor\bin\phpunit tests/Unit/CustomerServiceTest.php
    ```

#### On Windows (PowerShell):
*   Run all tests:
    ```powershell
    .\vendor\bin\phpunit
    ```
*   Run a specific test class:
    ```powershell
    .\vendor\bin\phpunit tests/Unit/CustomerServiceTest.php
    ```

---

## Core Philosophy

Backend is responsible for:
*   Enforcing business rules and protecting domain integrity.
*   Validating all input parameter structures.
*   Providing clean port-adapter boundaries for ease of testing.
*   Returning structured contracts (View DTOs) instead of raw entities.
