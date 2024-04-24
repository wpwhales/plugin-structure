<?php if (!defined('ABSPATH')) die();?>


        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .error-container {
            text-align: center;
        }
        .error-code {
            font-size: 72px;
            color: #555;
        }
        .error-message {
            margin-bottom: 20px;
            color: #777;
        }
        .back-button {
            background-color: #4caf50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="error-container">
    <svg width="150" height="150" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M3.58579 2.58579C3.21071 2.96086 3 3.46043 3 4V20C3 21.0609 3.7375 21.9217 4.71071 21.9933C4.78986 22.0001 4.86915 22.0001 4.9483 21.9933C5.9215 21.9217 6.659 21.0609 6.659 20V5.41421L11.2929 10.0481C11.6834 10.4386 12.3166 10.4386 12.7071 10.0481L17.341 5.41421L18.434 6.50721C18.8091 6.88229 19.3087 7.09288 19.8485 7.09288C20.3883 7.09288 20.8879 6.88229 21.263 6.50721C21.6381 6.13214 21.8487 5.63256 21.8487 5.09288C21.8487 4.55321 21.6381 4.05364 21.263 3.67857C20.888 3.3035 20.3884 3.09288 19.8487 3.09288C19.3089 3.09288 18.8093 3.3035 18.4342 3.67857L16.0003 6.11242L11.793 10.3197C11.6131 10.4997 11.3694 10.5893 11.118 10.5893C10.8665 10.5893 10.6228 10.4997 10.4429 10.3197L7.99996 7.87679L4.29289 11.5839C3.90237 11.9744 3.26919 11.9744 2.87866 11.5839C2.48814 11.1934 2.48814 10.5602 2.87866 10.1697L7.29289 5.75545L3 5.75545C3 5.4627 3.10536 5.19107 3.29289 5.00354C3.48043 4.81601 3.75207 4.71065 4.04482 4.71065C4.33757 4.71065 4.6092 4.81601 4.79674 5.00354L8.50381 8.71061C8.68414 8.89095 8.89095 8.99776 9.14161 8.99996C9.39226 9.00216 9.60014 8.89944 9.78219 8.71739C9.96424 8.53534 10.0669 8.32747 10.0647 8.07681C10.0625 7.82615 9.9557 7.61932 9.77536 7.43898L4.71065 2.37428C4.33558 1.99921 3.83601 1.78863 3.29633 1.78863C2.75666 1.78863 2.25708 1.99921 1.882 2.37428C1.50693 2.74935 1.29635 3.24892 1.29635 3.78859C1.29635 4.32827 1.50693 4.82784 1.882 5.20291C2.06234 5.38325 2.31623 5.49294 2.58579 5.49294C2.85534 5.49294 3.10922 5.38325 3.28956 5.20291C3.46991 5.02257 3.5796 4.76868 3.5796 4.49913C3.5796 4.22957 3.46991 3.97568 3.28956 3.79534C3.10922 3.615 2.85534 3.50531 2.58579 3.50531C2.31623 3.50531 2.06234 3.615 1.882 3.79534C1.70166 3.97568 1.59197 4.22957 1.59197 4.49913C1.59197 4.76868 1.70166 5.02257 1.882 5.20291C2.06117 5.38296 2.31438 5.49129 2.58289 5.49294H2.58579Z" fill="#FF4136"/>
    </svg>
    <div class="error-code">403</div>
    <div class="error-message">Not Allowed</div>
    <a class="back-button" href="{{home_url()}}">Go Back</a>
</div>
</body>

