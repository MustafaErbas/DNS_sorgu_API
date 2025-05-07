<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DNS Sorgu Aracı</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f8f9fa;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>DNS Sorgu Aracı</h1>
    <form id="dnsForm">
        <label for="domain">Domain:</label>
        <input type="text" id="domain" name="domain" required>

        <label for="queryType">Sorgu Türü:</label>
        <select id="queryType" name="type" required>
            <option value="">Seçiniz</option>
            <option value="A">A</option>
            <option value="MX">MX</option>
            <option value="TXT">TXT</option>
            <option value="SPF">SPF</option>
            <option value="CNAME">CNAME</option>
            <option value="AAAA">AAAA</option>
            <option value="SRV">SRV</option>
            <option value="SOA">SOA</option>
            <option value="ALL">ALL</option>
        </select>

        <button type="submit">Sorgula</button>
    </form>

    <div id="result" class="result"></div>
</div>

<script>
    document.getElementById('dnsForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const domain = document.getElementById('domain').value;
        const queryType = document.getElementById('queryType').value;

        fetch('DNS_Api.php?type=' + encodeURIComponent(queryType), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'domain=' + encodeURIComponent(domain),
        })
            .then(response => response.json())
            .then(data => {
                let resultDiv = document.getElementById('result');
                if (data.warnings) {
                    resultDiv.innerHTML = '<div class="error">' + data.warnings.join('<br>') + '</div>';
                } else if (data.error) {
                    resultDiv.innerHTML = '<div class="error">' + data.error + '</div>';
                } else if (Array.isArray(data) && data.length > 0) {
                    let resultHtml = '';
                    data.forEach(record => {
                        if (['A', 'MX', 'AAAA', 'SOA'].includes(queryType)) {
                            for (const [key, value] of Object.entries(record)) {
                                resultHtml += `<p><strong>${key}:</strong> ${value || ''}</p>`;
                            }
                        } else {
                            resultHtml += `<p>${JSON.stringify(record, null, 2)}</p>`;
                        }
                        resultHtml += '<hr>'; // Her kayıt arasına bir ayırıcı ekler.
                    });
                    resultDiv.innerHTML = resultHtml;
                } else {
                    resultDiv.innerHTML = 'Sonuç bulunamadı.';
                }
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<div class="error">Bir hata oluştu: ' + error.message + '</div>';
            });
    });


</script>
</body>
</html>
