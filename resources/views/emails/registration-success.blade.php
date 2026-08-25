<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Berhasil</title>

    <!-- custom css -->
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f5f7fa;
            color: #333333;
        }

        .email-container {
            width: 100%;
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .email-card {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .email-body {
            padding: 40px;
        }

        .app-name {
            margin: 0 0 30px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #212529;
        }

        .email-title {
            margin: 0 0 20px;
            font-size: 20px;
            font-weight: 600;
            color: #212529;
        }

        .email-text {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.7;
            color: #495057;
        }

        .email-footer {
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            color: #6c757d;
            font-size: 13px;
        }

        .email-footer p {
            margin: 0;
        }

        @media (max-width: 600px) {
            .email-container {
                padding: 20px 10px;
            }

            .email-body {
                padding: 30px 20px;
            }

            .app-name {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-card">
            <div class="email-body">
                <h2 class="app-name"> Student Career Center </h2>
                <h4 class="email-title"> Registrasi Berhasil </h4>
                <p class="email-text"> Halo, <strong>{{ $user->name }}</strong>. </p>
                <p class="email-text"> Selamat! Registrasi akun Anda di <strong>Student Career Center (SCC)</strong> telah berhasil. </p>
                <p class="email-text"> Anda sekarang dapat menggunakan akun untuk mengakses berbagai fitur yang tersedia di aplikasi SCC. </p>
                <p class="email-text"> Terima kasih telah menggunakan <strong>Student Career Center</strong>. </p>
            </div>
            <div class="email-footer"> <small> &copy; {{ date('Y') }} Student Career Center
                </small> </div>
        </div>
    </div>
</body>
</html>
