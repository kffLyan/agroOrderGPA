<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi AgroOrder GPA</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 40px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <!-- Brand Header (DeepSeek / Minimalist Style) -->
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <div style="display: inline-block; font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">
                                agroorder <span style="display: inline-block; background-color: #1B4307; color: #ffffff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px; vertical-align: middle; margin-left: 4px; letter-spacing: 0.5px;">GPA</span>
                            </div>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td>
                            <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 0 0 28px 0;">
                        </td>
                    </tr>

                    <!-- Greeting & Instruction -->
                    <tr>
                        <td align="center" style="padding-bottom: 28px;">
                            <p style="font-size: 14px; font-weight: 600; color: #0f172a; margin: 0 0 12px 0;">
                                Halo, <span style="color: #2563eb; text-decoration: underline;">{{ $recipientEmail }}</span>
                            </p>
                            <p style="font-size: 13px; line-height: 1.6; color: #475569; margin: 0; max-width: 420px;">
                                Untuk melanjutkan pendaftaran akun AgroOrder GPA Anda, silakan verifikasi akun Anda dengan kode di bawah ini:
                            </p>
                        </td>
                    </tr>

                    <!-- Large Clean OTP Code (Direct & To the point) -->
                    <tr>
                        <td align="center" style="padding-bottom: 28px;">
                            <div style="font-size: 44px; font-weight: 700; letter-spacing: 6px; color: #0f172a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1;">
                                {{ $otpCode }}
                            </div>
                        </td>
                    </tr>

                    <!-- Expiration & Security Notice -->
                    <tr>
                        <td align="center" style="padding-bottom: 28px;">
                            <p style="font-size: 12px; color: #64748b; line-height: 1.6; margin: 0 0 4px 0;">
                                Kode ini akan kedaluwarsa dalam <strong>5 menit</strong>. Harap jangan bagikan kode ini kepada orang lain.
                            </p>
                            <p style="font-size: 12px; color: #94a3b8; line-height: 1.6; margin: 0;">
                                Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini.
                            </p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td>
                            <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 0 0 20px 0;">
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center">
                            <p style="font-size: 11px; color: #94a3b8; margin: 0; line-height: 1.5;">
                                &copy; {{ date('Y') }} PT Green Pasundan Agriculture. All Rights Reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
