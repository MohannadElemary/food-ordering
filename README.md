## Overview

This project involves creating a system that handles orders for a products consisting of multiple ingredients. The system needs to manage stock levels for each ingredient, update stock when orders are placed, and send notifications when stock levels fall below a specified threshold. The implementation is built using Laravel v11 and a MySQL database, considering optimizations, performance, and clean code practices.

## Implementation Summary

We implemented the system with three main models: Product, Ingredient, and Order. The relationships between these models were established using pivot tables for many-to-many relationships. Key functionalities include:
- Accepting order details and updating stock levels.
- Sending notifications when stock levels fall below 50%.
- Maintaining clean code and performance optimization through caching, proper relationships, and query handling.

## How To Run
- Copy `.env.example` to a new file `.env`.
- `docker-compose up` to build and run the project.
- `docker-compose exec app bash` to enter the main container
- Run `php artisan migrate`
- Run `php artisan db:seed` (you can run it more and more whenever you want to fill-up your stocks)
- Run `php artisan db:seed --class=ConfigSeeder`
- Run `php artisan key:generate`
- Run `php artisan queue:work`


## Postman Documentation
- https://documenter.getpostman.com/view/8868758/2sA3QwcVTm

## Important Links:
- API Base Url: http://localhost:8080
- PHPMyAdmin: http://localhost:8082/ (root, secret)
- Telescope: http://localhost:8080/telescope
- Email Faker: http://localhost:8025/

## Project Files

### Models
1. **Ingredient Model** (`app/Models/Ingredient.php`)
    - Represents the ingredients in the system.

2. **Product Model** (`app/Models/Product.php`)
    - Represents products (burgers) in the system. Implements relationships with ingredients.

3. **Order Model** (`app/Models/Order.php`)
    - Represents orders in the system. Implements relationships with products.

4. **Config Model** (`app/Models/Config.php`)
    - Represents configuration settings like merchant email in the system.

### Services
1. **Config Service** (`app/Services/ConfigService.php`)
    - Manages configuration settings retrieval and caching.

2. **Notification Service** (`app/Services/NotificationService.php`)
    - Handles sending email notifications for low stock alerts.

3. **Stock Service** (`app/Services/StockService.php`)
    - Manages stock updates and tracks low stock ingredients.

4. **Order Service** (`app/Services/OrderService.php`)
    - Orchestrates the entire order process, including stock updates and notifications.

### Mail
1. **Stock Alert Mail** (`app/Mail/StockAlertMail.php`)
    - Mailable class responsible for creating the stock alert email (Queueable).

### Tests
1. **Order Service Unit Test** (`tests/Unit/OrderServiceTest.php`)
    - Unit tests for the OrderService to ensure correct functionality.

2. **Config Service Unit Test** (`tests/Unit/ConfigServiceTest.php`)
    - Unit tests for the ConfigService to ensure correct functionality.

3. **Notification Service Unit Test** (`tests/Unit/NotificationServiceTest.php`)
    - Unit tests for the NotificationService to ensure correct functionality.

4. **Stock Service Unit Test** (`tests/Unit/StockServiceTest.php`)
    - Unit tests for the StockService to ensure correct functionality.

5. **Order Workflow Integration Test** (`tests/Feature/OrderWorkflowTest.php`)
    - Integration tests for the entire order workflow, ensuring all services work together correctly.

### Controller
1. **OrderController** (`app/Http/Controllers/OrderController.php`)
    - Handles incoming API requests related to orders (Action Controller).

## Considerations for Optimizations and Best Practices

### Query Optimizations
- **Eager Loading**: Used eager loading for relationships to avoid N+1 query problems.
- **Indexing**: Ensured proper indexing on frequently queried columns.

### Performance Optimization
- **Caching**: Cached configuration settings to reduce database hits.
- **Transactions**: Used database transactions to ensure data consistency and atomic operations during order processing.

### Security
- **Validation**: Ensured request payloads are validated using Laravel's form request validation.
- **Rate Limiting**: Implemented rate limiting on API endpoints to prevent abuse.
- **Environment Configuration**: Used environment variables for sensitive configurations.

### Coding Best Practices
- **SOLID Principles**: Adhered to SOLID principles by creating service interfaces and ensuring single responsibility.
- **Dependency Injection**: Used dependency injection for better testability and maintainability.
- **Clean Code**: Maintained clean and readable code with appropriate comments and documentation.

---

## Notes
- The feature was created in just one commit message. Commiting every feature (task) in one commit instead of multiple makes it easier to trace and having a clean history.
- Redis was used for queues. But in real life applications, it's recommended to use other queuing system that is more efficient depends on the feature.
- There are more work to do to make the task more logical, but I didn't consider it to avoid over-engineering to doing unnecessary tasks that is more required (for example: API for listing products).
- We used the default queue for sending emails. In real-life applications, it's recommended to create multiple queues with different names for each feature or background job type depends on the features.
- It's recommended to version the endpoints in real-life applications. It was skipped in this task to avoid over-engineering.
- It's recommended to localize labels in real-life applications if the app is most likely to become multilanguage. It was skipped in this task to avoid over-engineering.
- There are more work to do to make the task more logical, but I didn't consider it to avoid over-engineering to doing unnecessary tasks that is more required (for example: protecting the endpoint with authentication).
- Using repositories was considered in the task, but it's too simple to do so. It will be just an extra layer that duplicates the model's functions.
- We have a queue jobs that needs the queue command to be running. As we are using docker, it's recommended to create a separated container that only runs that queue command. But was skipped in the task for avoiding unnecessary\unrelated implementations.
