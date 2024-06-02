<!DOCTYPE html>
<html>
<head>
    <title>Stock Alert</title>
</head>
<body>
<h1>Stock Alert</h1>
<p>Dear Merchant,</p>
<p>The following ingredients have fallen below 50% of their initial stock. Please restock soon:</p>
<ul>
    @foreach ($ingredients as $ingredient)
        <li>{{ $ingredient->name }}: {{ $ingredient->quantity }} grams remaining</li>
    @endforeach
</ul>
</body>
</html>
