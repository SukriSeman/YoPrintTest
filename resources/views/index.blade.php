<!DOCTYPE html>
<html>

<head>
    <title>CSV Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="p-5">

    <div class="container">
        <h3>CSV Upload</h3>

        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="file" name="file" class="form-control" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>

        <hr>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>File Name</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Uploaded At</th>
                </tr>
            </thead>
            <tbody id="uploadTable">
            </tbody>
        </table>
    </div>

    <script>
        function loadUploads() {
            $.get('/uploads', function(response) {
                const tbody = $('#uploadTable');
                tbody.empty();

                $.each(response.data, function(i, item) {
                    tbody.append(`
                    <tr id="row-${item.id}">
                        <td>${i + 1}</td>
                        <td>${item.filename}</td>
                        <td class="file_progress">${item.progress}%</td>
                        <td class="status">${item.status}</td>
                        <td>${item.uploaded_at}</td>
                    </tr>
                `);
                });
            });
        }

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: '/upload',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhrFields: { withCredentials: true },
                success: function() {
                    loadUploads();
                },
                error: function(xhr) {
                    alert('Upload failed!');
                }
            });
        });

        $(document).ready(loadUploads);
    </script>
    @vite(['resources/js/app.js'])

</body>

</html>
