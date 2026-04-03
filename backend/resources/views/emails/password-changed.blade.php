<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Password Was Changed</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 32px; margin-top: 20px;">
        <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">Your Password Was Changed</h1>

        <p style="color: #4b5563; margin: 0 0 24px 0;">
            Hi {{ $name }},
        </p>

        <p style="color: #4b5563; margin: 0 0 24px 0;">
            Your password was successfully changed. If you did not make this change, contact support immediately.
        </p>

        <div style="margin: 32px 0;">
            <a href="{{ config('app.frontend_url') }}/login" style="display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
                Go to Login
            </a>
        </div>

        <p style="color: #9ca3af; font-size: 12px; margin: 32px 0 0 0; border-top: 1px solid #e5e7eb; padding-top: 16px;">
            This is an automated message. Please do not reply to this email.
        </p>
    </div>
</body>
</html>