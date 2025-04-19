# URL Shortener Documentation

## System Overview

This URL shortener application allows users to create shortened links and earn money when people click on those links. The system includes user authentication, link management, click tracking, earnings calculation, and a referral program.

## Database Structure

The application uses the following database tables:

### users
- `id` - Primary key
- `username` - User's username
- `email` - User's email address
- `password` - Hashed password
- `balance` - User's current balance
- `referral_code` - Unique referral code
- `referred_by` - ID of the user who referred this user
- `created_at` - Registration timestamp

### links
- `id` - Primary key
- `user_id` - Foreign key to users table
- `original_url` - Original long URL
- `short_code` - Unique short code
- `title` - Optional title for the link
- `clicks` - Number of clicks
- `created_at` - Creation timestamp

### earnings
- `id` - Primary key
- `user_id` - Foreign key to users table
- `link_id` - Foreign key to links table
- `amount` - Earning amount
- `referral_amount` - Commission amount for referrer
- `referral_to` - ID of the referrer
- `ip_address` - IP address of the clicker
- `created_at` - Timestamp

### withdrawals
- `id` - Primary key
- `user_id` - Foreign key to users table
- `amount` - Withdrawal amount
- `payment_method` - Payment method
- `payment_details` - Payment details
- `status` - Status (pending, approved, rejected)
- `created_at` - Request timestamp
- `processed_at` - Processing timestamp

## Core Functions

### User Management
- User registration with referral tracking
- User authentication
- Profile management

### Link Management
- Creating short links with unique codes
- Tracking link clicks
- Managing user's links

### Earnings System
- Calculating earnings per click
- Tracking referral commissions
- Daily click limits per IP address

### Withdrawal System
- Requesting withdrawals
- Processing withdrawal requests
- Minimum withdrawal threshold

## Security Considerations

- Passwords are hashed using PHP's password_hash function
- SQL injection prevention using prepared statements
- XSS prevention by escaping output
- CSRF protection for forms
- Rate limiting for link creation

## Customization

### Earnings Rate
You can adjust the earnings rate per click by modifying the `RATE_PER_CLICK` constant in the config file.

### Referral Commission
The referral commission percentage can be adjusted by modifying the `REFERRAL_COMMISSION` constant in the config file.

### Daily Link Limit
The maximum number of links a user can create per day is controlled by the `MAX_DAILY_LINKS` constant in the config file.

## API Integration

The system can be extended to include API endpoints for:
- Creating short links programmatically
- Retrieving link statistics
- Managing user accounts

## Troubleshooting

### Common Issues
- Database connection errors: Check your database credentials in the config file
- Permission issues: Ensure proper file permissions for the web server
- Session problems: Verify that PHP sessions are properly configured

### Logging
The system logs errors to help with debugging. Check the error logs for more information about any issues.

## Future Enhancements
- API for programmatic access
- Advanced analytics for links
- Multiple payment methods for withdrawals
- Custom domains for short links
- Link expiration options