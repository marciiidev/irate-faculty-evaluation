<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Data | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-green-50 min-h-screen flex flex-col lg:flex-row text-green-950">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        <div class="p-4 md:p-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-green-950">Backup Data</h1>
                <p class="text-green-700">Restore deleted records or delete them permanently.</p>
            </div>

            <div class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-8 border-b border-green-100 flex justify-between items-center bg-orange-50/30">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600">
                            <i data-lucide="trash-2" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">Trash / Deleted Records</h2>
                            <p class="text-xs text-orange-600 font-bold tracking-wide uppercase">Records here are hidden but can be recovered.</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-green-50 border-b border-green-100 sticky top-0 z-10">
                                <th class="px-6 py-4 text-xs font-bold text-green-800 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-xs font-bold text-green-800 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-4 text-xs font-bold text-green-800 uppercase tracking-wider text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $deleted_items = [];
                            
                            // Students
                            $q = $conn->query("SELECT id, firstname, lastname, school_id as identifier, 'student' as type FROM student_list WHERE is_deleted = 1");
                            while($r = $q->fetch_assoc()) $deleted_items[] = $r;
                            
                            // Faculties
                            $q = $conn->query("SELECT id, firstname, lastname, school_id as identifier, 'faculty' as type FROM faculty_list WHERE is_deleted = 1");
                            while($r = $q->fetch_assoc()) $deleted_items[] = $r;
                            
                            // Academic
                            $q = $conn->query("SELECT id, CONCAT(year, ' ', semester, ' Semester') as firstname, '' as lastname, id as identifier, 'academic' as type FROM academic_list WHERE is_deleted = 1");
                            while($r = $q->fetch_assoc()) $deleted_items[] = $r;

                            // Classes
                            $q = $conn->query("SELECT id, class_name as firstname, '' as lastname, class_name as identifier, 'class' as type FROM class_list WHERE is_deleted = 1");
                            while($r = $q->fetch_assoc()) $deleted_items[] = $r;

                            // Subjects
                            $q = $conn->query("SELECT id, subject_name as firstname, '' as lastname, subject_code as identifier, 'subject' as type FROM subject_list WHERE is_deleted = 1");
                            while($r = $q->fetch_assoc()) $deleted_items[] = $r;

                            // Criteria
                            $q = $conn->query("SELECT id, criteria as firstname, '' as lastname, order_by as identifier, 'criteria' as type FROM criteria_list WHERE is_deleted = 1");
                            while($r = $q->fetch_assoc()) $deleted_items[] = $r;

                            // Questions
                            $q = $conn->query("SELECT id, question as firstname, '' as lastname, id as identifier, 'question' as type FROM question_list WHERE is_deleted = 1");
                            while($r = $q->fetch_assoc()) $deleted_items[] = $r;

                            // Restrictions
                            $q = $conn->query("SELECT r.id, CONCAT(a.year, ' - ', f.lastname) as firstname, '' as lastname, r.id as identifier, 'restriction' as type FROM restriction_list r LEFT JOIN academic_list a ON r.academic_id = a.id LEFT JOIN faculty_list f ON r.faculty_id = f.id WHERE r.is_deleted = 1");
                            while($r = $q->fetch_assoc()) $deleted_items[] = $r;

                            if (empty($deleted_items)):
                            ?>
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-green-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <i data-lucide="archive" class="w-8 h-8 opacity-20"></i>
                                        <p>Your trash is empty.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($deleted_items as $item): ?>
                                <tr class="border-b border-green-100 hover:bg-orange-50 transition-all group">
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-md text-[10px] font-bold uppercase tracking-wider">
                                            <?php echo $item['type'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-green-950"><?php echo trim($item['firstname'].' '.$item['lastname']) ?></span>
                                            <span class="text-xs text-green-600 font-mono">
                                                <?php echo $item['identifier'] ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="restoreRecord(<?php echo $item['id'] ?>, '<?php echo $item['type'] ?>')" class="bg-green-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-700 transition-all flex items-center gap-2 transition-transform hover:scale-105">
                                                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Restore
                                            </button>
                                            <button onclick="permDelete(<?php echo $item['id'] ?>, '<?php echo $item['type'] ?>')" class="bg-red-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-red-700 transition-all flex items-center gap-2 transition-transform hover:scale-105">
                                                <i data-lucide="trash-2" class="w-3 h-3"></i> Permanent Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        lucide.createIcons();

        function restoreRecord(id, type) {
            Swal.fire({
                title: 'Restore Record?',
                text: "This will bring the record back to the system active list.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#052e16',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Yes, Restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('type', type);
                    
                    fetch('../ajax.php?action=restore_data', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data == 1) {
                            Swal.fire('Restored!', 'Record has been successfully restored.', 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Error', 'Failed to restore record.', 'error');
                        }
                    });
                }
            })
        }

        function permDelete(id, type) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action is PERMANENT and CANNOT be reversed. Do you want to continue?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Yes, DELETE permanently'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('type', type);
                    
                    fetch('../ajax.php?action=perm_delete_data', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data == 1) {
                            Swal.fire('Deleted!', 'Record has been permanently removed.', 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Error', 'Failed to delete record. ' + data, 'error');
                        }
                    });
                }
            })
        }
    </script>
</body>
</html>
