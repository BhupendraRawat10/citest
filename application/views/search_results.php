<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .image-card {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .image-card img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .image-card p {
            margin-top: 10px;
            color: #666;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 40px;
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h2>Search Results</h2>
    
    <?php if (!empty($results)): ?>
        <div class="results-grid">
            <?php foreach ($results as $result): ?>
                <div class="image-card">
                    <img src="<?= $result->webformatURL ?>" alt="<?= htmlspecialchars($result->tags) ?>">
                    <p><?= htmlspecialchars($result->tags) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center;">No results found for your search.</p>
    <?php endif; ?>

    <a class="back-link" href="<?= base_url('search') ?>">← Back to Search</a>

</body>
</html>
