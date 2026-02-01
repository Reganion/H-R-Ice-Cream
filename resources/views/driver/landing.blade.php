<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Driver - Quinjay Ice Cream</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            padding: 24px;
        }
        .driver-box {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
            max-width: 400px;
        }
        .driver-box h1 { color: #1B1B1B; font-size: 24px; margin-bottom: 12px; }
        .driver-box p { color: #666; margin-bottom: 24px; }
        .driver-box a {
            display: inline-block;
            padding: 12px 24px;
            background: #E3001B;
            color: #fff;
            text-decoration: none;
            border-radius: 999px;
            font-weight: 600;
        }
        .driver-box a:hover { background: #c40000; }
    </style>
</head>

<body>
    <div class="driver-box">
        <h1>Driver</h1>
        <p>Driver login and dashboard will be available here.</p>
        <a href="{{ route('landing') }}">Back to home</a>
    </div>
</body>

</html>
