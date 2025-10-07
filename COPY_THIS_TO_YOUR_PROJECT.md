# EXACTLY WHAT TO COPY - Session 1 (UPDATED)

## CRITICAL: Migration Files with Current Timestamps

All migration files now have current timestamps and include safety checks to prevent errors if columns already exist.

---

## NEW MIGRATION FILES TO COPY (7 Files)

Copy these NEW files to your test project's `database/migrations/` folder:

```
1. database/migrations/2025_10_07_093650_update_food_menus_table.php
2. database/migrations/2025_10_07_093651_update_food_orders_table.php
3. database/migrations/2025_10_07_093652_update_food_order_items_table.php
4. database/migrations/2025_10_07_093653_add_order_id_to_wallet_transactions.php
5. database/migrations/2025_10_07_093654_update_riders_table.php
6. database/migrations/2025_10_07_084640_enhance_food_vendors_table.php
7. database/migrations/2025_10_07_093655_add_indexes_to_food_tables.php
```

---

## MODIFIED FILE TO UPDATE (1 Model File)

Replace this ENTIRE file in your test project:

```
FROM: app/Models/FoodVendor.php
TO:   app/Models/FoodVendor.php
```

---

## WHAT EACH MIGRATION DOES

### 1. `2025_10_07_093650_update_food_menus_table.php`
Adds to `food_menus` table:
- slug
- preparation_time_minutes
- category
- is_available
- image_urls (JSON)
- tags (JSON)

### 2. `2025_10_07_093651_update_food_orders_table.php`
Adds to `food_orders` table:
- tip_amount
- delivery_fee
- commission_amount
- payment_status (enum)
- payment_reference
- delivery_method (enum)
- shipping_address (JSON)
- rider_id (foreign key)
- Updates status enum with all order statuses

### 3. `2025_10_07_093652_update_food_order_items_table.php`
Adds to `food_order_items` table:
- total_price

### 4. `2025_10_07_093653_add_order_id_to_wallet_transactions.php`
Adds to `wallet_transactions` table:
- order_id
- order_type
- performed_by
- description
- related_type
- related_id

### 5. `2025_10_07_093654_update_riders_table.php`
Adds to `riders` table:
- is_available
- current_latitude
- current_longitude

### 6. `2025_10_07_084640_enhance_food_vendors_table.php`
Adds to `food_vendors` table:
- operating_hours (JSON)
- delivery_radius_km
- minimum_order_amount
- delivery_fee
- is_open
- accepts_cash
- accepts_card
- average_rating
- total_reviews
- total_orders
- latitude
- longitude
- cuisines (JSON)
- estimated_preparation_time

### 7. `2025_10_07_093655_add_indexes_to_food_tables.php`
Adds performance indexes to:
- food_menus
- food_orders
- food_order_items
- wallet_transactions
- food_vendors
- riders

---

## SAFETY FEATURES

All migrations now include:
- ✅ Column existence checks (`Schema::hasColumn()`)
- ✅ Index existence checks (custom helper method)
- ✅ Won't fail if columns/indexes already exist
- ✅ Safe to run multiple times
- ✅ Proper rollback support

---

## MIGRATION ORDER (Automatic)

The migrations will run in this order:
1. 2025_10_07_084640 - Enhance food_vendors
2. 2025_10_07_093650 - Update food_menus
3. 2025_10_07_093651 - Update food_orders
4. 2025_10_07_093652 - Update food_order_items
5. 2025_10_07_093653 - Update wallet_transactions
6. 2025_10_07_093654 - Update riders
7. 2025_10_07_093655 - Add indexes (MUST BE LAST)

---

## AFTER COPYING, RUN:

```bash
php artisan migrate
```

Expected output:
```
INFO  Running migrations.

2025_10_07_084640_enhance_food_vendors_table .......... DONE
2025_10_07_093650_update_food_menus_table ............. DONE
2025_10_07_093651_update_food_orders_table ............ DONE
2025_10_07_093652_update_food_order_items_table ....... DONE
2025_10_07_093653_add_order_id_to_wallet_transactions . DONE
2025_10_07_093654_update_riders_table ................. DONE
2025_10_07_093655_add_indexes_to_food_tables .......... DONE
```

---

## IF YOU GET "NOTHING TO MIGRATE"

This means the migration files already ran. Check your migrations table:

```sql
SELECT migration FROM migrations
WHERE migration LIKE '2025_10_07%'
ORDER BY migration;
```

If you see old timestamps (like 092253), delete them and run migrate again:

```sql
DELETE FROM migrations
WHERE migration IN (
    '2025_10_07_092253_createenhance_food_vendors_table',
    '2025_10_07_092411_create_add_indexes_to_food_tables'
);
```

Then run:
```bash
php artisan migrate
```

---

## ROLLBACK IF NEEDED

If you need to rollback these migrations:

```bash
php artisan migrate:rollback --step=7
```

This will remove all 7 migrations in reverse order.

---

## DOCUMENTATION FILES (Reference Only)

- `FOOD_DELIVERY_API.md` - Full API documentation for Postman testing
- `FOOD_DELIVERY_SESSION_1.md` - Complete session details
- `SESSION_1_CHECKLIST.md` - Testing checklist

---

## TOTAL FILES TO COPY: 8

7 Migration Files + 1 Model File = 8 Files Total

All migration files have current timestamps and will run in the correct order!
