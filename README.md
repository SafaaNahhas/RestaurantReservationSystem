## **Restaurant Reservation System**

This project aims to streamline the restaurant experience by providing high-level accessibility to restaurant information, facilitating comfortable reservation processes, and enabling electronic payments through a variety of services. Users can explore the restaurant, make reservations, and enjoy a seamless dining experience.

---

### **Access Control Summary**

#### **Admin Privileges:**

1. **User Management**:
    - Add, delete, and restore users.
    - Assign roles to users.
2. **Table Management**:
    - Add, edit, delete, and restore tables.
3. **Restaurant Information Management**:
    - Add, edit, delete, and restore restaurant information and images.
4. **Rating Management**:
    - Delete and restore ratings.
5. **Department Management**:
    - Add, edit, delete, and restore departments and departments images.
6. **Dish Management**:
    - View, add, edit, delete, and restore dishes and dishes images.
7. **Dish Category Management**:
    - View, add, edit, delete, and restore dish categories.
8. **Favorites Management**:
    - Delete and restore favorite dishes category and tables.
9. **Event Management**:
    - Add, edit, delete, and restore events.
10. **Emergency Management**:
    - Add, edit, and delete emergency situations.
11. **Notifications Log Management**:
    - View and delete notifications logs.
12. **Permission Management**:
    - Add, edit, and delete permissions.
13. **Role Management**:
    - Add, edit, and delete roles and their associated permissions.

#### **Manager Privileges:**

1. **Reservation Management**:
    - Confirm and reject reservations.
    - Make reservations for all departments.
    - View and edit reservations.
    - Delete and restore reservations.

#### **Customer Privileges:**

1. **Personal Information Management**:
    - Create an account.
    - View personal information.
2. **Restaurant Information **:
    - View restaurant information and tables.
3. **Reservation Management**:
    - Create, edit, and cancel reservations.
4. **Rating Management**
    - Create, edit, and delete reservation ratings.
5. **Dish Viewing**:
    - View dishes by category.
6. **Favorites Management**:
    - Add and remove dishs categories and tables to/from favorites.
7. **Department Viewing**:
    - View different departments.
8. **Electronic Payment**:
    - Pay the restaurant bill electronically.
9. **Table Viewing**:
    - View tables available during a specific time

#### **Waiter Privileges:**

1. **Reservation Handling**:
    - Confirm customer arrival.
    - Complete reservation to free up the table for reuse.

---

### **Account Management:**

-   Log in, log out, and change password.

---

### **Notifications and Emails:**

Users can choose their preferred notification method (email or Telegram notifications) through their account settings.

1. **Email Notifications**:
    - Send notifications to the manager when a reservation is made.
    - Send notifications to users when reservation confirmation or rejection.
    - Send notifications to users to rate the reservation when it is completed.
    - Send notifications to users of emergencies or specific events in the restaurant to cancel reservations.
    - Send notifications to users when the Event creeate or updated.
    - Send a daily report to the Admin about the restaurant's status.
    - Send a daily report to each Manager about their respective department's status.

---

### **Prerequisites:**

-   PHP >= 8.0
-   Composer
-   Laravel >= 9.0
-   MySQL or any database supported by Laravel
-   Postman for API testing

---

### **Advanced Security Features:**

-   **JWT Authentication**: Secure access to the API using JWT tokens.
-   **Rate Limiting**: Protect the API from DDoS attacks through rate limiting.
-   **CSRF Protection**: Ensure protection against Cross-Site Request Forgery (CSRF) attacks.
-   **XSS and SQL Injection Protection**: Utilize Laravel's built-in mechanisms to prevent XSS and SQL Injection attacks.

---

### **Steps to Run the Project:**

1. **Clone the Repository**:

    ```sh
    git clone https://github.com/SafaaNahhas/RestaurantReservationSystem
    ```

2. **Navigate to the Project Directory**:

    ```sh
    cd RestaurantReservationSystem
    ```

3. **Install Dependencies**:

    ```sh
    composer install
    ```

4. **Create Environment File**:

    ```sh
    cp .env.example .env
    ```

5. **Update the .env File** with your database configuration (MySQL credentials, database name, etc.).

6. **Generate Application Key**:

    ```sh
    php artisan key:generate
    ```

7. **Generate JWT Secret Key**:

    ```sh
    php artisan jwt:secret
    ```

8. **Run Migrations**:

    ```sh
    php artisan migrate
    ```

9. **Seed the Database**:

    ```sh
    php artisan db:seed
    ```

10. **Run the Job Queue**:

    ```sh
    php artisan queue:work
    ```

11. **Run the Application**:
    ```sh
    php artisan serve
    ```

---

### **Steps to Use Telegram Notifications**:

1. Send "start" to [@userinfobot](https://t.me/userinfobot).
2. Send "start" to [@TheReservationBot_bot](https://t.me/TheReservationBot_bot).
3. Select "Telegram notification" to activate notifications for your account.

---

### **Important Notes**:

-   Pay attention to the validation instructions in the request file for each operation you want to perform.
-   Test your work manually using Postman or HTTP.
-   You are welcome to create additional files.
-   Follow best practices to produce clean and professional results.

---

### **Postman Documentation**:

[Documentation Link](https://documenter.getpostman.com/view/34501481/2sAYJ4k1ig)

---

### **Team Members**:

-   [Safaa Nahaas (Team Leader)](https://github.com/SafaaNahhas)
-   [Haider Rayya (Assistant Team Leader)](https://github.com/HaidarRayya)
-   [Hiba Altabal](https://github.com/hiba-altabbal95)
-   [Hussein Hamda](https://github.com/HusseinIte)
-   [Khatoon Badre](https://github.com/KhatoonBadrea)
-   [Mohamed Karakit](https://github.com/Dralve)
-   [Mohammed Almostfa](https://github.com/MohammedAlmostfa)
-   [Youssef Alkurddi](https://github.com/Youssef2524)

---

Thank you for using our services.
