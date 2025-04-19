# URLSHORT

© 2024 HDEVON. All rights reserved. Unauthorized copying, distribution, or cloning is prohibited.

**Notice: This project is proprietary and not available for public download. Please contact the owner for purchase or access.**
**Violations will be subject to DMCA takedown actions.**

**Monitoring:**  
This project contains unique identifiers for tracking and monitoring unauthorized distribution or leaks.

A PHP-based URL shortener application that allows users to create short links, track clicks, and earn money from each click. The system also includes a referral program where users can earn commission from referrals.

## Features

- User registration and authentication
- Create short links with custom titles
- Track link clicks and statistics
- Earn money for each unique click
- Referral system with commission earnings
- Dashboard with earnings overview
- Withdrawal system for cashing out earnings

## Requirements

- PHP 7.4 or higher
- MySQL/MariaDB database
- Web server (Apache, Nginx, etc.)
- PDO PHP extension

## Installation

1. Clone or download this repository to your web server directory
2. Create a new MySQL/MariaDB database
3. Import the `database.sql` file to create the necessary tables
4. Configure your database connection in `config/config.php`
5. Make sure your web server has proper permissions to access the files
6. Access the application through your web browser

## Configuration

Edit the `config/config.php` file to set up your application:

- Database connection details
- Site name and URL
- Earnings rate per click
- Referral commission percentage
- Maximum daily links limit

## Usage

1. Register for an account
2. Log in to your dashboard
3. Create short links using the "Create Link" page
4. Share your links to earn money from clicks
5. Invite friends using your referral link to earn commission
6. Request withdrawals when you reach the minimum payout threshold

## License

This project is licensed under the MIT License - see the LICENSE file for details.
