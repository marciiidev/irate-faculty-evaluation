<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

$academic_id = $_GET['academic_id'] ?? 0;
if ($academic_id == 0) {
    $def_acad = $conn->query("SELECT id FROM academic_list WHERE is_default = 1 LIMIT 1")->fetch_assoc();
    if ($def_acad) {
        $academic_id = $def_acad['id'];
    } else {
        $last_acad = $conn->query("SELECT id FROM academic_list ORDER BY year DESC, semester DESC LIMIT 1")->fetch_assoc();
        if ($last_acad) {
            $academic_id = $last_acad['id'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionnaire | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-green-50 min-h-screen flex flex-col lg:flex-row text-green-950">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        <div class="p-4 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6 md:gap-8">
                <div>
                    <h1 class="text-2xl font-bold text-green-950">Evaluation Questionnaire</h1>
                    <p class="text-green-700 mb-2 text-sm">Manage evaluation questions. <span class="text-xs text-green-500 font-normal block mt-1">CSV Format: Question, Criteria Order</span></p>
                    
                    <div class="flex flex-col md:flex-row md:items-center gap-2 mt-4">
                        <label class="text-xs font-bold text-green-800 uppercase whitespace-nowrap">Manage for Period:</label>
                        <select onchange="location.href = 'questionnaire.php?academic_id=' + this.value" class="w-full md:w-auto px-3 py-1.5 rounded-lg border border-green-200 text-sm focus:ring-2 focus:ring-green-950 outline-none bg-white text-green-900 font-medium md:min-w-[200px]">
                            <?php
                            $acad_list = $conn->query("SELECT * FROM academic_list ORDER BY year DESC, semester DESC");
                            while($a = $acad_list->fetch_assoc()):
                                $is_default = $a['is_default'] ? ' (Default)' : '';
                            ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo ($academic_id == $a['id']) ? 'selected' : ''; ?>>
                                <?php echo $a['year'].' '.(($a['semester']==1)?'1st':'2nd').' Sem'.$is_default; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <a href="../superadmin/export_csv.php?type=questionnaire&academic_id=<?php echo $academic_id; ?>" class="bg-green-100 text-green-800 px-4 py-2 rounded-xl font-bold hover:bg-green-200 transition-all flex items-center gap-2 text-sm flex-1 md:flex-none justify-center">
                        <i data-lucide="download" class="w-4 h-4"></i> Export CSV
                    </a>
                    <button onclick="document.getElementById('import_csv').click()" class="bg-green-100 text-green-800 px-4 py-2 rounded-xl font-bold hover:bg-green-200 transition-all flex items-center gap-2 text-sm flex-1 md:flex-none justify-center">
                        <i data-lucide="upload" class="w-4 h-4"></i> Bulk Import CSV
                    </button>
                    <input type="file" id="import_csv" class="hidden" accept=".csv">
                    <button onclick="openModal()" class="bg-green-950 text-white px-4 py-2 rounded-xl font-bold hover:bg-green-900 transition-all text-sm flex-1 md:flex-none justify-center whitespace-nowrap">
                        Add New Question
                    </button>
                </div>
            </div>

            <div class="space-y-8">
                <?php
                $criteria_res = $conn->query("SELECT * FROM criteria_list ORDER BY order_by ASC");
                while($crit = $criteria_res->fetch_assoc()):
                ?>
                <div class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
                    <div class="bg-green-50 px-6 py-4 border-b border-green-200 flex justify-between items-center">
                        <h3 class="font-bold text-green-950">
                            <?php echo $crit['criteria']; ?>
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[500px]">
                            <thead>
                                <tr class="bg-green-50/50 border-b border-green-100">
                                    <th class="px-6 py-2 text-[10px] font-bold text-green-600 uppercase tracking-wider">Question</th>
                                    <th class="px-6 py-2 text-[10px] font-bold text-green-600 uppercase tracking-wider text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q_stmt = $conn->prepare("SELECT * FROM question_list WHERE criteria_id = ? AND academic_id = ? AND is_deleted = 0 ORDER BY order_by ASC");
                                $q_stmt->bind_param("ii", $crit['id'], $academic_id);
                                $q_stmt->execute();
                                $q_res = $q_stmt->get_result();
                                if($q_res->num_rows == 0):
                                ?>
                                <tr><td colspan="2" class="px-6 py-4 text-sm text-green-400 italic">No questions added yet.</td></tr>
                                <?php else: while($q = $q_res->fetch_assoc()): ?>
                                <tr class="border-b border-green-100 hover:bg-green-50 transition-all">
                                    <td class="px-6 py-4 text-sm text-green-950"><?php echo $q['question']; ?></td>
                                    <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                        <button onclick='openModal(<?php echo json_encode($q); ?>)' class="text-green-600 font-bold hover:underline">Edit</button>
                                        <span class="mx-2 text-green-300">|</span>
                                        <button onclick="deleteQuestion(<?php echo $q['id']; ?>)" class="text-red-600 font-bold hover:underline">Delete</button>
                                    </td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div id="questionModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-8 border-b border-green-100 flex justify-between items-center">
                <h2 id="modalTitle" class="text-xl font-bold text-green-950">Add New Question</h2>
                <button onclick="closeModal()" class="text-green-400 hover:text-green-950"><i data-lucide="x"></i></button>
            </div>
            <form id="questionForm" class="p-8 space-y-4">
                <input type="hidden" name="id" id="question_id">
                <input type="hidden" name="academic_id" value="<?php echo $academic_id; ?>">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-green-700 uppercase">Criteria</label>
                    <select name="criteria_id" id="criteria_id" required class="w-full px-4 py-2 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none transition-all">
                        <?php
                        $c_res = $conn->query("SELECT * FROM criteria_list ORDER BY order_by ASC");
                        while($c = $c_res->fetch_assoc()):
                        ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo $c['criteria']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-green-700 uppercase">Question</label>
                    <textarea name="question" id="question_text" required class="w-full px-4 py-2 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none transition-all" placeholder="Enter question..."></textarea>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-green-950 text-white py-3 rounded-xl font-bold hover:bg-green-900 transition-all">Save Question</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        document.getElementById('import_csv').onchange = function() {
            const academic_id = '<?php echo $academic_id; ?>';
            if (academic_id == '0' || academic_id == '') {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'No Academic Period', 
                    text: 'Please add an academic year/semester first before importing questions.' 
                });
                this.value = '';
                return;
            }

            if (this.files[0]) {
                const formData = new FormData();
                formData.append('csv_file', this.files[0]);
                formData.append('type', 'questionnaire');
                formData.append('academic_id', '<?php echo $academic_id; ?>');

                Swal.fire({
                    title: 'Importing Questions...',
                    text: 'Updating questionnaire categories and questions.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('../ajax.php?action=import_csv', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    if (data == 1) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: 'Questions imported successfully!' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'info', title: 'Import Result', html: data, confirmButtonText: 'Close' });
                    }
                });
            }
        };

        function openModal(data = null) {
            const modal = document.getElementById('questionModal');
            const form = document.getElementById('questionForm');
            const title = document.getElementById('modalTitle');
            
            form.reset();
            document.getElementById('question_id').value = '';
            
            if (data) {
                title.innerText = 'Edit Question';
                document.getElementById('question_id').value = data.id;
                document.getElementById('criteria_id').value = data.criteria_id;
                document.getElementById('question_text').value = data.question;
            } else {
                title.innerText = 'Add New Question';
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('questionModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('questionForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../ajax.php?action=save_question', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if (data == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Question has been saved successfully.',
                        confirmButtonColor: '#052e16'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data,
                        confirmButtonColor: '#052e16'
                    });
                }
            });
        };

        function deleteQuestion(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#052e16',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    fetch('../ajax.php?action=delete_question', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Question has been deleted.',
                                confirmButtonColor: '#052e16'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data,
                                confirmButtonColor: '#052e16'
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
