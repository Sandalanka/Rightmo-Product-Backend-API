<?php

namespace App\Constants;

class MessageConstant
{
    // General MessageConstant.
    const GENERAL_RESPONSE_ERROR_MESSAGE = 'Something went wrong!';

    const SOMETHING_WENT_WRONG = 'Something went wrong.';

    // Product messages
    const PRODUCTS_FETCHED = 'Products fetched successfully.';

    const PRODUCT_FETCHED = 'Product fetched successfully.';

    const PRODUCT_CREATED = 'Product created successfully.';

    const PRODUCT_UPDATED = 'Product updated successfully.';

    const PRODUCT_DELETED = 'Product deleted successfully.';

    const PRODUCT_NOT_FOUND = 'Product not found.';

    // Product image messages
    const PRODUCT_IMAGES_ADDED = 'Product images added successfully.';

    const PRODUCT_IMAGE_UPDATED = 'Product image updated successfully.';

    const PRODUCT_IMAGE_DELETED = 'Product image deleted successfully.';

    const PRODUCT_IMAGE_NOT_FOUND = 'Product image not found.';

    // Product rating messages
    const PRODUCT_RATINGS_FETCHED = 'Product ratings fetched successfully.';

    const PRODUCT_RATED = 'Product rated successfully.';

    const PRODUCT_RATING_UPDATED = 'Product rating updated successfully.';

    const PRODUCT_RATING_DELETED = 'Product rating deleted successfully.';

    const PRODUCT_RATING_NOT_FOUND = 'Product rating not found.';

    const PRODUCT_RATING_FORBIDDEN = 'You can only change your own ratings.';

    // Category messages
    const CATEGORIES_FETCHED = 'Categories fetched successfully.';

    // Auth messages
    const USER_REGISTERED = 'User registered successfully.';

    const USER_LOGIN = 'User login successfully.';

    const INVALID_CREDENTIALS = 'Invalid email or password.';

    const USER_LOGOUT = 'User logout successfully.';

    const UNAUTHENTICATED = 'Unauthenticated. Please login and send a valid Bearer token.';
}
