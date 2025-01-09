Restauant Reaervayion System
This project aims to streamline the restaurant experience by providing a high level of accessibility to restaurant information, facilitating comfortable reservation processes, and enabling electronic payments through a variety of services. Users can explore the restaurant, make reservations, and enjoy a seamless dining experience.

#

## Access Control Summary

### Admin Privileges:

1. **User Management**:
    - Add, delete, and restore users.
    - Assign roles to users.
2. **Table Management**:
    - Add, edit, delete, and restore tables.
3. **Restaurant Information Management**:
    - Add, edit, delete, and restore restaurant information and images.
4. **Review Management**:
    - Delete and restore reviews.
5. **Department Management**:
    - Add, edit, delete, and restore departments.
6. **Dish Management**:
    - View, add, edit, delete, and restore dishes.
7. **Category Management**:
    - View, add, edit, delete, and restore dish categories.
8. **Favorites Management**:
    - Delete and restore favorite dishes and tables.
9. **Event Management**:
    - Add, edit, delete, and restore events.
10. **Emergency Management**:
    - Add, edit, and delete emergency situations.
11. **Email Log Management**:
    - View and delete email logs.
12. **Permission Management**:
    - Add, edit, delete, and restore permissions.
13. **Role Management**:
    - Add and edit roles and their associated permissions.

### Manager Privileges:

1. **Reservation Management**:
    - Confirm and reject reservations.
    - Make reservations for all departments.
    - View and edit reservations.
    - Delete and restore reservations.

### User Privileges:

1. **Personal Information**:
    - Create an account, log in, and log out.
    - View personal information .
2. **Restaurant Information**:
    - View restaurant information, tables, and reviews.
3. **Reservation Actions**:
    - Create, edit, and cancel reservations.
4. **Dish Management**:
    - View dishes by category.
5. **Favorites Management**:
    - Add and remove dishes and tables to/from favorites.
6. **Department Viewing**:
    - View different departments.
7. **Electronic Payment**:
    - Pay the restaurant bill electronically.

### Waiter Privileges:

1. **Reservation Handling**:
    - Confirm customer arrival.
    - Complete reservation to free up the table for reuse.

### Notifications and Emails:

1. **Email Notifications**:
    - Send email notifications to the manager when a reservation is made.
    - Notify users via email upon reservation confirmation or rejection.
    - Notify users of emergencies or specific events.
    - Send a daily report to the Admin about the restaurant's status.
    - Send a daily report to each Manager about their respective department's status.

## Prerequisites

-   PHP >= 8.0
-   Composer
-   Laravel >= 9.0
-   MySQL or any database supported by Laravel
-   Postman for API testing

## Advanced Security Features

-   **JWT Authentication**: Secure access to the API using JWT tokens.
-   **Rate Limiting**: Protect the API from DDoS attacks through rate limiting.
-   **CSRF Protection**: Ensure protection against Cross-Site Request Forgery (CSRF) attacks.
-   **XSS and SQL Injection Protection**: Utilize Laravel's built-in mechanisms to prevent XSS and SQL Injection attacks.

## Steps to Run the Project

1. **Clone the Repository**
    ```sh
    git clone https://github.com/SafaaNahhas/RestaurantReservationSystem
    ```
2. **Navigate to the Project Directory**
    ```sh
    cd RestaurantReservationSystem
    ```
3. **Install Dependencies**
    ```sh
    composer install
    ```
4. **Create Environment File**
    ```sh
    cp .env.example .env
    ```
5. **Update the .env File** with your database configuration (MySQL credentials, database name, etc.).
6. **Generate Application Key**
    ```sh
    php artisan key:generate
    ```
7. **Run Migrations**
    ```sh
    php artisan migrate
    ```
8. **Seed the Database**
    ```sh
    php artisan db:seed
    ```
9. **Run the Job Queue**
    ```sh
    php artisan queue:work
    ```
10. **Run the Application**
    ```sh
    php artisan serve
    ```

## Important Notes

-   Pay attention to the validation instructions in the request file for each operation you want to perform.
-   Test your work manually using Postman or HTTP.
-   You are welcome to create additional files.
-   Follow best practices to produce clean and professional results.

## Postman:

[Documentation Link](https://documenter.getpostman.com/view/34501481/2sAYJ4k1ig)

## Team Members

-   [Safaa Nahaas (Team Leader)](https://github.com/SafaaNahhas)
-   [Haider Rayya](https://github.com/HaidarRayya)
-   [Hiba Altabal](https://github.com/hiba-altabbal95)
-   [Hussein Hamda](https://github.com/HusseinIte)
-   [Khatoon Badre](https://github.com/KhatoonBadrea)
-   [Mohamed Karakit](https://github.com/Dralve)
-   [Mohammed Almostfa](https://github.com/MohammedAlmostfa)
-   [Youssef Alkurddi](https://github.com/Youssef2524)

Thank you for using our services.
