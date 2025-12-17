<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - HotSpotz</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #722F37;
        }
        h1 {
            color: #722F37;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 20px;
            color: #555;
        }
        .button {
            display: inline-block;
            background-color: #722F37;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #5a252c;
        }
        .cta-container {
            text-align: center;
            margin: 30px 0;
        }
        .link-text {
            word-break: break-all;
            font-size: 12px;
            color: #888;
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #888;
            font-size: 12px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 12px;
            margin: 20px 0;
            font-size: 14px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"><img src="{{ asset('assets/hotSpotzLogo.png') }}" alt="HotSpotz" style="height: 32px; vertical-align: middle; margin-right: 8px;"></div>
        </div>

        <h1>Reset Your Password</h1>

        <p>Hello {{ $mailData['name'] }},</p>

        <p>We received a request to reset the password for your HotSpotz account. Click the button below to create a new password:</p>

        <div class="cta-container">
            <a href="{{ $mailData['resetUrl'] }}" class="button">Reset Password</a>
        </div>

        <div class="warning">
            <strong>This link expires in 60 minutes.</strong> If you didn't request a password reset, you can safely ignore this email.
        </div>

        <p class="link-text">
            If the button doesn't work, copy and paste this link into your browser:<br>
            {{ $mailData['resetUrl'] }}
        </p>

        <div class="footer">
            <p>This email was sent by HotSpotz. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} HotSpotz. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
