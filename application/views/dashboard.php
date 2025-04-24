<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 20px;
        }
        h2 {
            margin: 0;
            font-size: 24px;
        }
        img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-top: 10px;
        }
        nav {
            margin-top: 20px;
            text-align: center;
        }
        nav ul {
            list-style: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin: 0 15px;
        }
        nav ul li a {
            text-decoration: none;
            color: #333;
            font-size: 18px;
            padding: 10px 20px;
            background-color: #e7e7e7;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        nav ul li a:hover {
            background-color: #4CAF50;
            color: white;
        }
        .container {
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h2>Welcome, <?= $user['name'] ?></h2>
        <img src="<?= base_url('' . $user['profile_picture']) ?>" alt="Profile Picture">
    </header>

    <div class="container">
        <nav>
            <ul>
                <li><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li><a href="<?= base_url('profile') ?>">Profile</a></li>
                <li><a href="<?= base_url('search') ?>">Search</a></li>

                <li><a href="<?= base_url('logout') ?>">Logout</a></li>
            </ul>
        </nav>
    </div>
</body>
</html>
