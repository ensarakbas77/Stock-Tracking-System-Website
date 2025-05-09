<?php
require_once("aws_db.php");

$users = $conn->query("SELECT user_id, username, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Flowbite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #f8fafc, #e2e8f0);
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        .table th, .table td {
            text-align: center;
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }
        .table th {
            background: #1d4ed8;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .table th:hover {
            background: #1e40af;
        }
        .table td {
            font-size: 0.875rem;
            color: #1f2937;
        }
        .table tr {
            transition: background 0.2s ease;
        }
        .table tr:hover {
            background: #f1f5f9;
        }
        .search-bar {
            position: relative;
            max-width: 300px;
        }
        .search-bar input {
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            width: 100%;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .search-bar input:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
            outline: none;
        }
        .search-bar svg {
            position: absolute;
            top: 50%;
            left: 0.75rem;
            transform: translateY(-50%);
            color: #6b7280;
            width: 1.25rem;
            height: 1.25rem;
        }
        .action-btn {
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .action-btn:hover {
            transform: translateY(-1px);
        }
        .modal {
            transition: opacity 0.3s ease, transform 0.3s ease;
            transform: scale(0.95);
        }
        .modal:not(.hidden) {
            transform: scale(1);
        }
        h2 {
            font-weight: 600;
            font-size: 1.5rem;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-6">Kullanıcı Yönetimi</h2>

        <!-- Arama Çubuğu -->
        <div class="search-bar mb-6">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" id="searchInput" placeholder="Kullanıcı ara..." class="w-full">
        </div>

        <!-- Tablo Görünümü -->
        <div class="table-container overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Kullanıcı Numarası</th>
                        <th onclick="sortTable(1)">Kullanıcı Adı</th>
                        <th onclick="sortTable(2)">Rol</th>
                        <th>Aksiyonlar</th>
                    </tr>
                </thead>
                <tbody id="userTable">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <button onclick="openEditModal(<?php echo $user['user_id']; ?>)" class="action-btn bg-green-600 text-white">Düzenle</button>
                                <button onclick="openDeleteModal(<?php echo $user['user_id']; ?>)" class="action-btn bg-red-600 text-white">Sil</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Düzenle Modal -->
    <div id="editModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="modal-content bg-white p-5 rounded-lg w-full max-w-sm">
            <h3 class="text-lg font-medium mb-4 text-gray-900">Kullanıcıyı Düzenle</h3>
            <form action="edit_user.php" method="GET">
                <input type="hidden" id="editUserId" name="id">
                <div class="mb-4">
                    <label class="block text-sm text-gray-700 font-medium">Kullanıcı Adı</label>
                    <input type="text" name="username" class="w-full p-2 border rounded-lg text-sm" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-700 font-medium">Rol</label>
                    <input type="text" name="role" class="w-full p-2 border rounded-lg text-sm" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()" class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm">İptal</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sil Modal -->
    <div id="deleteModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="modal-content bg-white p-5 rounded-lg w-full max-w-sm">
            <h3 class="text-lg font-medium mb-4 text-gray-900">Kullanıcıyı Sil</h3>
            <p class="text-sm text-gray-600">Bu kullanıcıyı silmek istediğinizden emin misiniz?</p>
            <form action="delete_user.php" method="GET">
                <input type="hidden" id="deleteUserId" name="id">
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeDeleteModal()" class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm">İptal</button>
                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Sil</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flowbite JS -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script>
        // Arama
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#userTable tr');

            rows.forEach(row => {
                const username = row.cells[1].textContent.toLowerCase();
                row.style.display = username.includes(searchTerm) ? '' : 'none';
            });
        });

        // Tablo Sıralama
        function sortTable(column) {
            const table = document.getElementById('userTable');
            const rows = Array.from(table.rows);
            const isAscending = table.dataset.sort !== 'asc';
            table.dataset.sort = isAscending ? 'asc' : 'desc';

            rows.sort((a, b) => {
                const aValue = a.cells[column].textContent;
                const bValue = b.cells[column].textContent;
                return isAscending
                    ? aValue.localeCompare(bValue, undefined, { numeric: true })
                    : bValue.localeCompare(aValue, undefined, { numeric: true });
            });

            table.innerHTML = '';
            rows.forEach(row => table.appendChild(row));
        }

        // Modal İşlevleri
        function openEditModal(userId) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function openDeleteModal(userId) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</body>
</html>