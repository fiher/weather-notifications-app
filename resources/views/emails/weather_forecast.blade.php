<!DOCTYPE html>
<html>
<head>
    <title>Daily Weather Forecast</title>
</head>
<body>
    <h2>Weather Forecast for {{ $weatherData['location']['name'] }}</h2>
    <p><strong>Temperature:</strong> {{ $weatherData['current']['temperature'] }}°C</p>
    <p><strong>Weather:</strong> {{ $weatherData['current']['weather_descriptions'][0] }}</p>
    <p><strong>Humidity:</strong> {{ $weatherData['current']['humidity'] }}%</p>
    <p><strong>Wind Speed:</strong> {{ $weatherData['current']['wind_speed'] }} km/h</p>
    <br>
    <p>If you wish to unsubscribe, click <a href="{{ url('/api/subscriptions/unsubscribe/'.$weatherData['subscription_id']) }}">here</a>.</p>
</body>
</html> 