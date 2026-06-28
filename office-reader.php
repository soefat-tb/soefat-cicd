<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client-Side Word and Excel Reader</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        #fileInput {
            margin: 20px 0;
        }
        #output {
            border: 1px solid #ccc;
            padding: 15px;
            min-height: 200px;
            overflow: auto;
        }
        #error {
            color: red;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Client-Side Word and Excel Reader</h2>
        <input type="file" id="fileInput" accept=".docx,.xlsx">
        <div id="error"></div>
        <div id="output"></div>
    </div>

    <script>
        document.getElementById('fileInput').addEventListener('change', handleFileSelect, false);

        function handleFileSelect(event) {
            const file = event.target.files[0];
            const outputDiv = document.getElementById('output');
            const errorDiv = document.getElementById('error');

            if (!file) {
                errorDiv.textContent = 'No file selected.';
                errorDiv.style.display = 'block';
                outputDiv.innerHTML = '';
                return;
            }

            errorDiv.style.display = 'none';
            outputDiv.innerHTML = 'Processing...';

            const reader = new FileReader();

            reader.onload = function(e) {
                const arrayBuffer = e.target.result;

                if (file.name.endsWith('.docx')) {
                    mammoth.convertToHtml({ arrayBuffer: arrayBuffer })
                        .then(result => {
                            outputDiv.innerHTML = result.value;
                            if (result.messages.length > 0) {
                                errorDiv.textContent = 'Warnings: ' + result.messages.map(m => m.message).join('; ');
                                errorDiv.style.display = 'block';
                            }
                        })
                        .catch(err => {
                            errorDiv.textContent = 'Error reading Word file: ' + err.message;
                            errorDiv.style.display = 'block';
                            outputDiv.innerHTML = '';
                        });
                } else if (file.name.endsWith('.xlsx')) {
                    try {
                        const workbook = XLSX.read(new Uint8Array(arrayBuffer), { type: 'array' });
                        let htmlOutput = '';
                        workbook.SheetNames.forEach(sheetName => {
                            const sheet = workbook.Sheets[sheetName];
                            htmlOutput += `<h3>Sheet: ${sheetName}</h3>`;
                            htmlOutput += XLSX.utils.sheet_to_html(sheet);
                        });
                        outputDiv.innerHTML = htmlOutput;
                    } catch (err) {
                        errorDiv.textContent = 'Error reading Excel file: ' + err.message;
                        errorDiv.style.display = 'block';
                        outputDiv.innerHTML = '';
                    }
                } else {
                    errorDiv.textContent = 'Unsupported file type. Please upload a .docx or .xlsx file.';
                    errorDiv.style.display = 'block';
                    outputDiv.innerHTML = '';
                }
            };

            reader.onerror = function() {
                errorDiv.textContent = 'Error reading file.';
                errorDiv.style.display = 'block';
                outputDiv.innerHTML = '';
            };

            reader.readAsArrayBuffer(file);
        }
    </script>
</body>
</html>