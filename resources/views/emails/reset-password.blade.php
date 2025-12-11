<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f8; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; }
        .content { background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin: 40px auto; }
        .header { background: linear-gradient(135deg, #E53935 0%, #EF5350 100%); padding: 30px 20px; text-align: center; }
        .body-text { padding: 40px 30px; color: #333333; }
        .otp-box { 
            background-color: #FFEBEE; 
            color: #C62828; 
            font-size: 32px; 
            font-weight: 800; 
            letter-spacing: 8px; 
            text-align: center; 
            padding: 20px; 
            border-radius: 12px; 
            margin: 30px 0; 
            border: 2px dashed #EF9A9A;
        }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #999999; }
        .warning-box {
            background-color: #FFF3E0;
            border-left: 4px solid #FF9800;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        @media only screen and (max-width: 600px) {
            .content { margin: 20px; width: auto; }
            .body-text { padding: 20px; }
            .otp-box { font-size: 24px; letter-spacing: 4px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="content">
            
            <div class="header">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                    MONAS
                </h1>
            </div>

            <div class="body-text">
                <h2 style="margin-top: 0; color: #1a1a1a;">Reset Password Anda</h2>
                <p style="font-size: 16px; line-height: 1.6; color: #555;">
                    Kami menerima permintaan untuk mereset password akun Monas Anda. Untuk melanjutkan proses reset password, silakan gunakan kode OTP di bawah ini:
                </p>

                <div class="otp-box">
                    {{ $otp }}
                </div>

                <div class="warning-box">
                    <p style="margin: 0; font-size: 14px; color: #E65100; font-weight: 600;">
                        ⚠️ PENTING: 
                    </p>
                    <p style="margin: 5px 0 0 0; font-size: 13px; color: #E65100;">
                        Kode ini berlaku selama 15 menit. Jika Anda tidak meminta reset password, abaikan email ini dan password Anda tidak akan berubah.
                    </p>
                </div>

                <p style="font-size: 14px; color: #666; text-align: center; margin-top: 20px;">
                    <strong>Jangan berikan kode ini kepada siapapun.</strong>
                </p>

                <hr style="border: none; border-top: 1px solid #eeeeee; margin: 30px 0;">

                <p style="font-size: 12px; color: #888; text-align: center; margin-bottom: 0;">
                    Jika Anda tidak merasa melakukan permintaan reset password ini, silakan abaikan email ini atau hubungi tim support jika Anda merasa ada aktivitas mencurigakan pada akun Anda.
                </p>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} Monas App. All rights reserved.<br>
                Automated message, please do not reply.
            </div>
            
        </div>
    </div>

</body>
</html>

