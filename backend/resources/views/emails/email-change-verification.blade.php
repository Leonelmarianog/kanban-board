<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Email Change</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 32px; margin-top: 20px;">
        <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">Confirm Your Email Change</h1>

        <p style="color: #4b5563; margin: 0 0 24px 0;">
            A request has been made to change your email address to: <strong>{{ $newEmail }}</strong>
        </p>

        <p style="color: #4b5563; margin: 0 0 24px 0;">
            Please click the button below to confirm this change.
        </p>

        <div style="margin: 32px 0;">
            <a href="{{ $verificationUrl }}" style="display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
                Confirm Email Change
            </a>
        </div>

        <p style="color: #6b7280; font-size: 14px; margin: 0 0 24px 0;">
            This link will expire in {{ $expirationMinutes }} minutes.
        </p>

        <p style="color: #6b7280; font-size: 14px; margin: 0 0 24px 0;">
            If the button above doesn't work, copy and paste this URL into your browser:<br>
            <code style="word-break: break-all; background-color: #e5e7eb; padding: 2px 6px; border-radius: 4px;">{{ $verificationUrl }}</code>
        </p>

        <p style="color: #9ca3af; font-size: 12px; margin: 32px 0 0 0; border-top: 1px solid #e5e7eb; padding-top: 16px;">
            If you didn't request this change, you can safely ignore this email.
        </p>
    </div>
</body>
</html>