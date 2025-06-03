<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Maintenance</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e9f0ff;
        }

        header {
            padding: 20px 50px;
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #999;
            font-weight: 500;
        }

        nav a:last-child {
            color: #1e3a8a;
        }

        main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 40px 90px;
            background: #fff;
            border-radius: 8px;
            margin: 40px auto;
            width: 90%;
            max-width: 1200px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .text {
            max-width: 500px;
        }

        .text h1 {
            font-size: 48px;
            color: #1e3a8a;
            margin-bottom: 10px;
        }

        .text h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .text p {
            color: #666;
            line-height: 1.6;
        }

        .text a.button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background-color: #1e3a8a;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .illustration img {
            width: 500px;
            max-width: 100%;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            font-size: 14px;
            color: #999;
        }
    </style>
</head>

<body>

    <main>
        <div class="text">
            <h1>Oops!</h1>
            <h2>Under construction</h2>
            <p>Saat ini web sedang dilakukan maintenance, mohon kiranya untuk menunggu</p>
        </div>
        <div data-wow-delay="0.2s">
            <img
                src="{{asset('/dist/assets/images/mt.png')}}"
                alt="" />
        </div>
    </main>


</body>

</html>