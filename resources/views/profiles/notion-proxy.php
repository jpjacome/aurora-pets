<?php
// Set the API endpoint
$url = 'https://api.notion.com/v1/databases/1bb9fee5299280758926dc84fb33345c/query';

// Get the request body from the AJAX request
$requestBody = file_get_contents('php://input');

// Create a cURL handle
$ch = curl_init($url);

// Set the HTTP headers
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer secret_IT4gLeDhipjJRqRAqzrFJPSSRtJk1upaB5Czg5hbpV3',
    'Content-Type: application/json',
    'Notion-Version: 2021-08-16'
));

// Set the request method to POST
curl_setopt($ch, CURLOPT_POST, true);

// Set the request body
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);

// Return the response instead of outputting it
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL request
$response = curl_exec($ch);

// Close the cURL handle
curl_close($ch);

// Echo the response (which will send it to your front-end code)
echo $response;
?>