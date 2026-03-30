<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Change Request</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 32px; margin-top: 20px;">
        <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">Email Change Request</h1>

        <p style="color: #4b5563; margin: 0 0 24px 0;">
            Hi {{ $name }},
        </p>

        <p style="color: #4b5563; margin: 0 0 24px 0;">
            A request has been made to change your email address. Your new email address will be: <strong>{{ $newEmail }}</strong>
        </p>

        <p style="color: #4b5563; margin: 0 0 24px 0;">
            A confirmation email has been sent to your new email address. Once you confirm the change from that email, your email address will be updated and you will be logged out of all devices.
        </p>

        <p style="color: #4b5563; margin: 0 0 24px 0;">
            <strong>Didn't request this change?</strong> You can cancel the request by clicking the button below.
        </p>

        <div style="margin: 32px 0;">
            <a href="{{ $cancelUrl }}" style="display: inline-block; background-color: #dc2626; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
                Cancel Email Change
            </a>
        </div>

        <p style="color: #6b7280; font-size: 14px; margin: 0 0 24px 0;">
            This cancel link will expire in {{ $expirationMinutes }} minutes.
        </p>

        <p style="color: #6b7280; font-size: 14px; margin: 0 0 24px 0;">
            If the button above doesn't work, copy and paste this URL into your browser:<br>
            <code style="word-break: break-all; background-color: #e5e7eb; padding: 2px 6px; border-radius: 4px;">{{ $cancelUrl }}</code>
        </p>

        <p style="color: #9ca3af; font-size: 12px; margin: 32px 0 0 0; border-top: 1px solid #e5e7eb; padding-top: 16px;">
            This is an automated message. Please do not reply to this email.
        </p>
    </div>
</body>
</html>