<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1"
          name="viewport" />
    <meta content="SwaggerUI"
          name="description" />
    <title>JAF Parfum's API Documentation</title>
    <link href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css"
          rel="stylesheet" />
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"
            crossorigin></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: "{{ url('docs.json') }}",
                dom_id: '#swagger-ui',
            });
        };
    </script>
</body>

</html>
