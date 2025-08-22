<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary URL
    |--------------------------------------------------------------------------
    |
    | This is your full Cloudinary environment URL. You can get this from your
    | Cloudinary dashboard. It looks like:
    | cloudinary://API_KEY:API_SECRET@CLOUD_NAME
    |
    */
    'cloud_url' => env('CLOUDINARY_URL', sprintf(
        'cloudinary://%s:%s@%s',
        env('CLOUDINARY_API_KEY'),
        env('CLOUDINARY_API_SECRET'),
        env('CLOUDINARY_CLOUD_NAME')
    )),

    /*
    |--------------------------------------------------------------------------
    | Optional Upload Preset
    |--------------------------------------------------------------------------
    |
    | If you’ve configured an unsigned upload preset on Cloudinary for frontend
    | uploads (like with their widget), you can set it here.
    |
    */
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),

    /*
    |--------------------------------------------------------------------------
    | Notification URL (Optional)
    |--------------------------------------------------------------------------
    |
    | If you use Cloudinary’s asynchronous upload features and want to receive
    | webhooks upon success or failure, set your endpoint here.
    |
    */
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Optional Blade Upload Widget Support
    |--------------------------------------------------------------------------
    |
    | These are for advanced use cases where you use Cloudinary’s upload widget
    | in Blade views with helper routes/controllers. You can leave these empty.
    |
    */
    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),

];
