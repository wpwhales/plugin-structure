<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .header {
            font-size: 24px;
            color: #d9534f;
            margin-bottom: 20px;
        }

        .details {
            margin-bottom: 20px;
        }

        .details pre {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            overflow: auto;
        }

        .trace {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow: auto;
        }

        .trace-item {
            margin-bottom: 10px;
        }

        .trace-item pre {
            margin: 0;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
        }

        .trace-item code {
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">Exception Occurred</div>
    <div class="details">
        <strong>Message:</strong> <?= $exception->getMessage() ?><br>
        <strong>File:</strong> <?= $exception->getFile() ?><br>
        <strong>Line:</strong> <?= $exception->getLine() ?><br>
        <strong>Code:</strong> <?= $exception->getCode() ?><br>
    </div>
    <div class="trace">
        <strong>Trace:</strong>
        <?php foreach ($exception->getTrace() as $trace) { ?>
            <div class="trace-item">
                    <pre><code>
File: <?= $trace['file'] ?>
        Line: <?= $trace['line'] ?>
        Function: <?= $trace['function'] ?>
                            <?php if (isset($trace['class'])) { ?>
                                Class: <?= $trace['class'] ?>
                            <?php } ?>
        Type: <?= isset($trace['type']) ?? $trace['type'] ?>
        Args:
        <?php if(isset($trace['args'])){foreach ($trace['args'] as $arg) { ?>
            <?= is_array($arg) ? json_encode($arg):"MAY BE IT's a CLOSURE NEED SOMETHING TO HANDLE IT" ?>
        <?php }} ?>
        </code></pre>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
