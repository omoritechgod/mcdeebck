# Food Delivery API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication
Most endpoints require authentication using Laravel Sanctum. Include the token in the Authorization header:
```
Authorization: Bearer {your_token}
```

---

## Vendor Endpoints

### 1. Complete Food Vendor Setup
**Endpoint:** `POST /vendor/food/setup`
**Auth Required:** Yes (Vendor)
**Description:** Complete the food vendor profile setup

**Request Body:**
```json
{
    "business_name": "Mama Blessing Kitchen",
    "specialty": "Nigerian delicacies",
    "cuisines": ["Nigerian", "African", "Local"],
    "location": "Wuse 2, Abuja",
    "latitude": 9.0765,
    "longitude": 7.3986,
    "contact_phone": "08012345678",
    "contact_email": "mamablessing@gmail.com",
    "description": "We offer healthy home-cooked meals",
    "logo": "uploads/logos/mama.png",
    "operating_hours": {
        "monday": {"open": "08:00", "close": "20:00"},
        "tuesday": {"open": "08:00", "close": "20:00"},
        "wednesday": {"open": "08:00", "close": "20:00"},
        "thursday": {"open": "08:00", "close": "20:00"},
        "friday": {"open": "08:00", "close": "20:00"},
        "saturday": {"open": "09:00", "close": "18:00"},
        "sunday": {"closed": true}
    },
    "delivery_radius_km": 5.5,
    "minimum_order_amount": 2000,
    "delivery_fee": 500,
    "estimated_preparation_time": 45,
    "accepts_cash": true,
    "accepts_card": true,
    "is_open": true
}
```

**Response (201):**
```json
{
    "message": "Food vendor profile completed",
    "data": {
        "id": 1,
        "vendor_id": 12,
        "business_name": "Mama Blessing Kitchen",
        "specialty": "Nigerian delicacies",
        "cuisines": ["Nigerian", "African", "Local"],
        "location": "Wuse 2, Abuja",
        "latitude": 9.0765,
        "longitude": 7.3986,
        "contact_phone": "08012345678",
        "contact_email": "mamablessing@gmail.com",
        "description": "We offer healthy home-cooked meals",
        "logo": "uploads/logos/mama.png",
        "operating_hours": {...},
        "delivery_radius_km": 5.50,
        "minimum_order_amount": 2000.00,
        "delivery_fee": 500.00,
        "is_open": true,
        "accepts_cash": true,
        "accepts_card": true,
        "average_rating": 0.00,
        "total_reviews": 0,
        "total_orders": 0,
        "estimated_preparation_time": 45,
        "created_at": "2025-10-07T08:00:00.000000Z",
        "updated_at": "2025-10-07T08:00:00.000000Z"
    }
}
```

### 2. Get Food Vendor Profile
**Endpoint:** `GET /vendor/food/profile`
**Auth Required:** Yes (Vendor)
**Description:** Get the authenticated vendor's food profile

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "vendor_id": 12,
        "business_name": "Mama Blessing Kitchen",
        "specialty": "Nigerian delicacies",
        "location": "Wuse 2, Abuja",
        "contact_phone": "08012345678",
        "contact_email": "mamablessing@gmail.com",
        "description": "We offer healthy home-cooked meals",
        "logo": "uploads/logos/mama.png"
    }
}
```

---

## Food Menu Endpoints

### 3. List All Food Menus (Public)
**Endpoint:** `GET /food/menus`
**Auth Required:** No
**Description:** Get all available food menu items

**Query Parameters:**
- `vendor_id` (optional) - Filter by vendor
- `category` (optional) - Filter by category
- `available` (optional) - Filter by availability (1 or 0)

**Response (200):**
```json
[
    {
        "id": 1,
        "vendor_id": 12,
        "name": "Jollof Rice with Chicken",
        "slug": "jollof-rice-with-chicken",
        "description": "Delicious Nigerian jollof rice served with grilled chicken",
        "price": 2500.00,
        "preparation_time_minutes": 30,
        "category": "Main Course",
        "is_available": true,
        "image": "uploads/menus/jollof.jpg",
        "image_urls": [
            "uploads/menus/jollof1.jpg",
            "uploads/menus/jollof2.jpg"
        ],
        "tags": ["spicy", "popular", "chicken"],
        "estimated_time": "30 mins",
        "created_at": "2025-10-07T08:00:00.000000Z",
        "updated_at": "2025-10-07T08:00:00.000000Z"
    }
]
```

### 4. Create Food Menu (Vendor Only)
**Endpoint:** `POST /food/menus`
**Auth Required:** Yes (Vendor)
**Description:** Create a new menu item

**Request Body:**
```json
{
    "name": "Jollof Rice with Chicken",
    "slug": "jollof-rice-with-chicken",
    "description": "Delicious Nigerian jollof rice served with grilled chicken",
    "price": 2500,
    "preparation_time_minutes": 30,
    "category": "Main Course",
    "is_available": true,
    "image": "uploads/menus/jollof.jpg",
    "image_urls": [
        "uploads/menus/jollof1.jpg",
        "uploads/menus/jollof2.jpg"
    ],
    "tags": ["spicy", "popular", "chicken"]
}
```

**Response (201):**
```json
{
    "id": 1,
    "vendor_id": 12,
    "name": "Jollof Rice with Chicken",
    "slug": "jollof-rice-with-chicken",
    "description": "Delicious Nigerian jollof rice served with grilled chicken",
    "price": 2500.00,
    "preparation_time_minutes": 30,
    "category": "Main Course",
    "is_available": true,
    "image": "uploads/menus/jollof.jpg",
    "image_urls": [...],
    "tags": [...],
    "created_at": "2025-10-07T08:00:00.000000Z",
    "updated_at": "2025-10-07T08:00:00.000000Z"
}
```

---

## Order Endpoints

### 5. Place a Food Order
**Endpoint:** `POST /food/orders`
**Auth Required:** Yes (User)
**Description:** Place a new food order

**Request Body:**
```json
{
    "vendor_id": 12,
    "items": [
        {
            "menu_id": 1,
            "quantity": 2
        },
        {
            "menu_id": 3,
            "quantity": 1
        }
    ],
    "delivery_method": "delivery",
    "shipping_address": {
        "street": "123 Main Street",
        "area": "Wuse 2",
        "city": "Abuja",
        "state": "FCT",
        "landmark": "Opposite Unity Bank",
        "phone": "08012345678",
        "latitude": 9.0765,
        "longitude": 7.3986
    },
    "tip_amount": 200
}
```

**Response (201):**
```json
{
    "message": "Order placed",
    "order_id": 45,
    "order": {
        "id": 45,
        "user_id": 5,
        "vendor_id": 12,
        "rider_id": null,
        "total": 5000.00,
        "tip_amount": 200.00,
        "delivery_fee": 500.00,
        "commission_amount": 250.00,
        "payment_status": "pending",
        "payment_reference": null,
        "status": "pending_payment",
        "delivery_method": "delivery",
        "shipping_address": {...},
        "can_show_contacts": false,
        "items": [
            {
                "id": 78,
                "food_order_id": 45,
                "food_menu_id": 1,
                "quantity": 2,
                "price": 2500.00,
                "total_price": 5000.00
            }
        ],
        "created_at": "2025-10-07T08:00:00.000000Z",
        "updated_at": "2025-10-07T08:00:00.000000Z"
    }
}
```

### 6. Get User's Food Orders
**Endpoint:** `GET /food/orders`
**Auth Required:** Yes (User)
**Description:** Get all orders for the authenticated user

**Query Parameters:**
- `status` (optional) - Filter by order status
- `payment_status` (optional) - Filter by payment status

**Response (200):**
```json
[
    {
        "id": 45,
        "user_id": 5,
        "vendor_id": 12,
        "rider_id": 8,
        "total": 5000.00,
        "tip_amount": 200.00,
        "delivery_fee": 500.00,
        "commission_amount": 250.00,
        "payment_status": "paid",
        "payment_reference": "FLWK-12345",
        "status": "preparing",
        "delivery_method": "delivery",
        "shipping_address": {...},
        "can_show_contacts": true,
        "items": [...],
        "vendor": {
            "id": 12,
            "business_name": "Mama Blessing Kitchen",
            "contact_phone": "08012345678"
        },
        "rider": {
            "id": 8,
            "user": {
                "name": "John Rider",
                "phone": "08087654321"
            }
        },
        "created_at": "2025-10-07T08:00:00.000000Z",
        "updated_at": "2025-10-07T08:00:00.000000Z"
    }
]
```

### 7. Update Order Status (Vendor Only)
**Endpoint:** `PUT /food/orders/{id}/status`
**Auth Required:** Yes (Vendor)
**Description:** Update the status of a food order

**Request Body:**
```json
{
    "status": "preparing"
}
```

**Valid Status Values:**
- `pending_payment` - Order created, awaiting payment
- `awaiting_vendor` - Payment received, vendor hasn't accepted
- `accepted` - Vendor accepted the order
- `preparing` - Food is being prepared
- `ready_for_pickup` - Food is ready for rider pickup
- `assigned` - Rider has been assigned
- `picked_up` - Rider picked up the order
- `on_the_way` - Order is in transit
- `delivered` - Order delivered to customer
- `completed` - Order completed successfully
- `cancelled` - Order was cancelled
- `disputed` - Order has a dispute

**Response (200):**
```json
{
    "message": "Order status updated",
    "order": {
        "id": 45,
        "status": "preparing",
        "updated_at": "2025-10-07T08:05:00.000000Z"
    }
}
```

---

## Order Flow Summary

### Standard Order Flow:
1. **Customer:** Places order → `pending_payment`
2. **Customer:** Pays for order → `awaiting_vendor`
3. **Vendor:** Accepts order → `accepted`
4. **Vendor:** Starts preparing → `preparing`
5. **Vendor:** Food ready → `ready_for_pickup`
6. **System:** Assigns rider → `assigned`
7. **Rider:** Picks up order → `picked_up`
8. **Rider:** En route to customer → `on_the_way`
9. **Rider:** Delivers to customer → `delivered`
10. **System:** Marks as complete → `completed`

### Pickup Order Flow:
1. **Customer:** Places order with pickup method → `pending_payment`
2. **Customer:** Pays for order → `awaiting_vendor`
3. **Vendor:** Accepts order → `accepted`
4. **Vendor:** Starts preparing → `preparing`
5. **Vendor:** Food ready → `ready_for_pickup`
6. **Customer:** Picks up order → `completed`

---

## Payment Status Values
- `pending` - Payment not yet made
- `paid` - Payment successful
- `refunded` - Payment was refunded

---

## Delivery Methods
- `delivery` - Standard delivery with rider
- `pickup` - Customer picks up from vendor
- `offline_rider` - Vendor uses their own delivery

---

## Testing Notes

### Environment Setup
1. Ensure `.env` file is configured with database credentials
2. Run migrations: `php artisan migrate`
3. Start server: `php artisan serve`

### Required Headers for Protected Endpoints
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {your_token}
```

### Test Data
You can use the database seeders to populate test data:
```bash
php artisan db:seed
```

---

## Error Responses

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
    "message": "Unauthorized or invalid vendor category"
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "price": ["The price must be a number."]
    }
}
```

### 404 Not Found
```json
{
    "message": "Resource not found"
}
```

---

## Postman Collection

Import the following collection into Postman for easy testing:

**Collection Name:** Food Delivery API

**Variables:**
- `base_url` = `http://localhost:8000/api`
- `token` = `your_auth_token_here`

All endpoints are pre-configured with example requests and responses.
