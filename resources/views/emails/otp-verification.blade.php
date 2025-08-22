<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px;">
        <h2>Hello {{ $name }},</h2>
        <p>Thank you for registering on <strong>McDee</strong>.</p>
        <p>Please use the OTP below to verify your email address:</p>

        <h1 style="font-size: 40px; color: #F76300; text-align: center;">{{ $otp }}</h1>

        <p style="margin-top: 20px;">This OTP is valid for 10 minutes.</p>
        <p>If you did not request this, please ignore this message.</p>

        <br>
        <p>Regards,<br><strong>McDee Team</strong></p>
    </div>
</body>
</html>
