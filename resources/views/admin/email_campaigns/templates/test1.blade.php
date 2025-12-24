<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Aurora!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inline critical styles for email compatibility -->
    <style>
        /* Basic reset and table layout for email */
        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            font-family: 'Buenard', serif, Arial, sans-serif;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }
        img {
            display: block;
            max-width: 100%;
            height: auto;
        }
        h1 {
            font-family: 'Playfair Display', serif, Arial, sans-serif;
            color: #00452A;
            font-size: 2.2em;
            margin: 0 0 10px 0;
        }
        p {
            color: #333;
            font-size: 1.1em;
            line-height: 1.5em;
            margin: 0 0 15px 0;
        }
        .btn {
            display: inline-block;
            background: #fe8d2c;
            color: #fff !important;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 1em;
            text-decoration: none;
            margin-top: 18px;
        }
        .footer {
            background: #fe8d2c;
            color: #fff;
            text-align: center;
            font-size: 0.95em;
            padding: 18px 0;
        }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f5f5f5; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.07);">
                    <tr>
                        <td style="padding: 40px 30px 20px 30px; text-align: center;">
                            <img src="{{ asset('images/logo.png') }}" alt="Aurora Logo" width="120" style="margin-bottom: 20px;">
                            <h1>Welcome, {{ $user->name ?? 'Friend' }}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 30px 30px 30px; color: #333; font-family: 'Buenard', serif;">
                            <p>Thank you for joining Aurora. We’re excited to have you on board!</p>
                            <p>Here’s what you can expect next:</p>
                            <ul style="color: #00452A; font-size: 1em; padding-left: 18px; margin: 0 0 15px 0;">
                                <li>Personalized plant care tips</li>
                                <li>Exclusive offers and updates</li>
                                <li>Access to our expert community</li>
                            </ul>
                            <a href="{{ url('/dashboard') }}" class="btn">Get Started</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} Aurora. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
