# WooCommerce Custom Review Management

A WooCommerce admins to manually add customer reviews for products from the WordPress admin dashboard. It provides an interface for admins to enter review details, including reviewer name, email, content, rating, and optional verification.

## Features

- Admins can add custom reviews directly from the WordPress admin area.
- Supports manual product selection with autocomplete functionality.
- Reviews can be marked as verified by the admin.
- Rating (1-5 stars) system with default value as 5 stars.
- Option to specify the review date and time.
- Displays success or error messages after review submission.

## Installation
 -- Add the code in theme functions.php file

## Usage

1. Navigate to the **Products** section in the WordPress admin area.
2. Click on the "Add Custom Review" submenu under "Products."
3. Fill in the product name, reviewer name, review content, rating, and other relevant fields.
4. Submit the review to save it as a WooCommerce product review.

### Adding Reviews

- **Product Name**: Begin typing the product name, and the plugin will suggest matching products.
- **Reviewer Name**: Enter the name of the reviewer.
- **Reviewer Email**: Optionally, enter the email address of the reviewer.
- **Review Content**: Write the review content.
- **Rating**: Select a rating between 1 and 5 stars.
- **Verified Review**: Optionally, mark the review as verified.
- **Review Date**: Select the date and time of the review.

## Hooks

- `admin_post_process_custom_review`: Handles form submission and processes the review data.
- `woocommerce_product_autocomplete`: Provides the product search functionality for autocomplete.

## Contributing

Feel free to contribute to this plugin by submitting a pull request. Please ensure your code follows the WordPress coding standards.
