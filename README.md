<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>
# Bakala Express

Bakala Express is a neighbourhood grocery delivery platform connecting local bakala shopkeepers with customers. The platform enables customers to browse nearby sellers, order daily essentials, and track deliveries in real time, while sellers manage products, orders, and earnings through a dedicated panel.

## Features

- **Seller Panel**: Allows laundry service providers to register, log in, add services, and manage their offerings.
- **Buyer Interface**: Customers can browse available services, view pricing, and avail of services with ease.
- **Service Management**: Sellers can add new services with details such as pricing, description, and images.
- **Authentication**: Secure user authentication for both buyers and sellers with separate login and registration forms.
- **Responsive Design**: Fully responsive UI for an optimal experience across all devices.

## Tech Stack

- **Backend**: Laravel (PHP)
- **Frontend**: HTML, CSS, JavaScript
- **Database**: MySQL
- **File Storage**: Local or cloud storage for service images
- **Authentication**: Laravel built-in authentication system

## Installation

To get started with the project locally:

1. Clone the repository:

   ```bash
   git clone https://github.com/yourusername/bakala-express.git
   
2. Navigate the Project Directory
   ```bash
   cd bakala-express
3. Install require Dependensies
   ```bash
   composer install
4. Set up .env file
   ```bash
   cp .env.example .env
5. Run Database Migrations
   ```bash
   php artisan migrate
6. now run the application
   ```bash
   php artisan serve

Now you should be able to access the application at http://localhost:8000.

Usage
Seller:

Register and log in to the seller panel.
Add new services (including images and descriptions).
View and manage existing services.
Buyer:

Browse available laundry services.
View detailed information about each service.
Avail of services based on pricing and location.