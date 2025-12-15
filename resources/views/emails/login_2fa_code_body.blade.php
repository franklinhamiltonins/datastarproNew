<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"  style="background:#ffffff;border-radius: 12px;">
                    <tr>
                        <td style="background-color: #1f78a0;padding: 16px 32px;font-size: 24px;line-height: 28px;font-weight: 700;color: #fff;text-align: center;border-top-right-radius: 12px;border-top-left-radius: 12px;">
                            Your Verification Code
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 16px;line-height: 24px;color: #333;padding: 32px;">
                           <p style="margin-bottom: 12px;"> Hi {{$data['name']}},</p>
                            <p>We have received a request to log in to the <strong>DatastarPro Dashboard</strong>.<br /> Please use the 2FA authentication code below to complete your sign-in.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 32px; text-align:center;">
                            <!-- <div style="font-size:22px; color:#333; font-weight:bold;">
                                🔐 Your Verification Code
                            </div> -->
                            <div style="background-color: #f4f4f4;padding: 14px 16px;font-size:32px;line-height: 36px;font-weight:bold;color:#1f78a0;letter-spacing: 16px;border-radius: 8px;margin-top:10px;">
                                {{$data['otp']}}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 16px;line-height: 24px;color: #333;padding: 32px;">
                            <p>If you did not attempt this login, please ignore this email or contact our support team immediately.</p>
                            <p>Thank you,<br /> <strong>DatastarPro Team</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
