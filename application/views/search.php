<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Image Search</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #eef2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .search-container {
      background-color: #fff;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 500px;
    }

    nav ul {
      list-style: none;
      display: flex;
      justify-content: space-around;
      margin-bottom: 20px;
    }

    nav ul li a {
      text-decoration: none;
      color: #007BFF;
      font-weight: 500;
      transition: color 0.3s;
    }

    nav ul li a:hover {
      color: #0056b3;
    }

    .search-container h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 26px;
      color: #333;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    label {
      font-weight: 500;
      color: #444;
    }

    input[type="text"] {
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      transition: border-color 0.3s;
    }

    input[type="text"]:focus {
      border-color: #007BFF;
      outline: none;
    }

    button {
      background-color: #007BFF;
      color: #fff;
      padding: 12px;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #0056b3;
    }

    @media (max-width: 600px) {
      .search-container {
        padding: 30px 20px;
      }

      .search-container h2 {
        font-size: 22px;
      }

      nav ul {
        flex-direction: column;
        gap: 10px;
        align-items: center;
      }
    }
    .back-link{
        padding:10px;
    }
  </style>
</head>
<body>

  <div class="search-container">
  
    <h2>Search for Images</h2>

    <form method="POST" action="<?= base_url('search/search_images') ?>">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>"/>

      <label for="query">Search Query</label>
      <input type="text" name="query" id="query" placeholder="Enter keywords..." required />

      <button type="submit">Search</button>
    </form>
    <a class="back-link" href="<?= base_url('dashboard') ?>">← Back to Dashboard</a>

  </div>

</body>
</html>
