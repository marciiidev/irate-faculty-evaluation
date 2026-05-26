<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$academic_id = isset($_GET['academic_id']) ? (int)$_GET['academic_id'] : 0;
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Get default academic year if not set
if ($academic_id == 0) {
    $def_acad = $conn->query("SELECT id FROM academic_list WHERE is_default = 1 LIMIT 1")->fetch_assoc();
    if ($def_acad) $academic_id = $def_acad['id'];
}

// Build Query
$where = " WHERE 1=1 ";
if ($academic_id > 0) $where .= " AND el.academic_id = $academic_id ";
if ($class_id > 0) $where .= " AND el.class_id = $class_id ";
if (!empty($search)) {
    $where .= " AND (s.school_id LIKE '%$search%' OR s.firstname LIKE '%$search%' OR s.lastname LIKE '%$search%' OR s.email LIKE '%$search%') ";
}

$query_str = "SELECT el.*, s.school_id, s.firstname as sf, s.lastname as sl, s.email, c.class_name, f.firstname as ff, f.lastname as fl, a.year, a.semester 
              FROM evaluation_list el 
              JOIN student_list s ON el.student_id = s.id 
              JOIN class_list c ON el.class_id = c.id 
              JOIN faculty_list f ON el.faculty_id = f.id 
              JOIN academic_list a ON el.academic_id = a.id 
              $where 
              ORDER BY el.date_created DESC";

$total_counts = $conn->query("SELECT COUNT(*) as count FROM evaluation_list el JOIN student_list s ON el.student_id = s.id $where")->fetch_assoc()['count'];
$total_pages = ceil($total_counts / $limit);

$evaluations = $conn->query("$query_str LIMIT $offset, $limit");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Progress | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-green-50 min-h-screen flex flex-col lg:flex-row text-green-950">
    <div class="no-print">
        <?php include 'sidebar.php'; ?>
    </div>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        
        <div class="p-4 md:p-8">
            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 md:gap-8">
                <div>
                    <h1 class="text-2xl font-bold text-green-950">Evaluation Progress</h1>
                    <p class="text-green-700">Track students who have completed their evaluations across semesters.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="exportCSV()" class="bg-green-100 text-green-700 px-4 py-2 rounded-xl font-bold hover:bg-green-200 transition-all flex items-center gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- Participation Analytics -->
            <?php
            if ($academic_id > 0):
                // 1. Get required evaluation counts per class
                $req_query = "SELECT class_id, COUNT(*) as req_count FROM restriction_list WHERE academic_id = $academic_id AND is_deleted = 0 GROUP BY class_id";
                $req_res = $conn->query($req_query);
                $class_requirements = [];
                while($rr = $req_res->fetch_assoc()) {
                    $class_requirements[$rr['class_id']] = $rr['req_count'];
                }

                // 2. Get students and their completion status
                $class_where = ($class_id > 0) ? " AND class_id = $class_id " : "";
                $students_res = $conn->query("SELECT id, class_id FROM student_list WHERE 1=1 $class_where");
                
                $total_students = $students_res->num_rows;
                $fully_evaluated_count = 0;
                $partially_evaluated_count = 0;
                $no_evaluations_count = 0;

                while($st = $students_res->fetch_assoc()) {
                    $sid = $st['id'];
                    $cid = $st['class_id'];
                    $required = isset($class_requirements[$cid]) ? $class_requirements[$cid] : 0;
                    
                    if ($required == 0) {
                        // Skip or handle students with no assigned evaluations
                        $total_students--;
                        continue;
                    }

                    $completed_res = $conn->query("SELECT COUNT(*) as count FROM evaluation_list WHERE student_id = $sid AND academic_id = $academic_id");
                    $completed = $completed_res->fetch_assoc()['count'];

                    if ($completed >= $required) $fully_evaluated_count++;
                    elseif ($completed > 0) $partially_evaluated_count++;
                    else $no_evaluations_count++;
                }

                $percentage = ($total_students > 0) ? round(($fully_evaluated_count / $total_students) * 100, 1) : 0;
                $participation_color = ($percentage >= 80) ? 'text-green-600' : (($percentage >= 50) ? 'text-amber-600' : 'text-red-600');
            ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="md:col-span-2 bg-white p-6 rounded-3xl border border-green-200 shadow-sm flex items-center justify-between overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-green-400 uppercase tracking-widest mb-1">Completion Progress</p>
                        <h3 class="text-4xl font-black <?php echo $participation_color; ?> mb-1"><?php echo $percentage; ?>%</h3>
                        <p class="text-xs text-green-900 font-medium italic">of students finished all assigned evaluations</p>
                    </div>
                    <div class="w-24 h-24 relative flex items-center justify-center shrink-0">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" class="text-gray-100" />
                            <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" stroke-dasharray="<?php echo 2 * pi() * 40; ?>" stroke-dashoffset="<?php echo (2 * pi() * 40) * (1 - ($percentage / 100)); ?>" class="<?php echo $participation_color; ?> transition-all duration-1000" />
                        </svg>
                        <i data-lucide="check-circle" class="w-8 h-8 absolute <?php echo $participation_color; ?>"></i>
                    </div>
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-green-50 rounded-full blur-2xl opacity-50 group-hover:scale-125 transition-transform"></div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-green-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-600">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-green-400 uppercase tracking-widest">Fully Evaluated</p>
                            <p class="text-2xl font-black text-green-950"><?php echo $fully_evaluated_count; ?> <span class="text-xs text-gray-400 font-medium">/ <?php echo $total_students; ?></span></p>
                        </div>
                    </div>
                    <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-green-600 h-full rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-green-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-amber-400 uppercase tracking-widest">In Progress</p>
                            <p class="text-2xl font-black text-green-950"><?php echo $partially_evaluated_count; ?></p>
                        </div>
                    </div>
                    <p class="text-[10px] text-amber-600 font-bold uppercase">Awaiting Completion</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white p-6 rounded-2xl border border-green-200 shadow-sm mb-8">
                <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-green-800 uppercase">Academic Year</label>
                        <select name="academic_id" onchange="this.form.submit()" class="w-full px-4 py-2 rounded-lg border border-green-200 focus:ring-2 focus:ring-green-950 outline-none">
                            <option value="0">All Semesters</option>
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
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-green-800 uppercase">Class</label>
                        <select name="class_id" onchange="this.form.submit()" class="w-full px-4 py-2 rounded-lg border border-green-200 focus:ring-2 focus:ring-green-950 outline-none">
                            <option value="0">All Classes</option>
                            <?php
                            $class_list = $conn->query("SELECT * FROM class_list ORDER BY class_name ASC");
                            while($c = $class_list->fetch_assoc()):
                            ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($class_id == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo $c['class_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-xs font-bold text-green-800 uppercase">Search</label>
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search by student name, ID or email..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-green-200 focus:ring-2 focus:ring-green-950 outline-none">
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="bg-green-50 border-b border-green-100">
                                <th class="px-6 py-4 text-xs font-bold text-green-700 uppercase">Student</th>
                                <th class="px-6 py-4 text-xs font-bold text-green-700 uppercase">Class</th>
                                <th class="px-6 py-4 text-xs font-bold text-green-700 uppercase">Target Faculty</th>
                                <th class="px-6 py-4 text-xs font-bold text-green-700 uppercase text-center">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-green-700 uppercase">Date Evaluated</th>
                                <th class="px-6 py-4 text-xs font-bold text-green-700 uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-green-50">
                            <?php if($evaluations->num_rows == 0): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No evaluation records found.</td>
                            </tr>
                            <?php else: while($row = $evaluations->fetch_assoc()): ?>
                            <tr class="hover:bg-green-50/50 transition-all group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-green-950"><?php echo $row['sf'].' '.$row['sl']; ?></span>
                                        <span class="text-xs text-gray-500"><?php echo $row['school_id']; ?> </span>
                                        <span class="text-xs text-gray-400"><?php echo $row['email']; ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        <?php echo $row['class_name']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-green-800"><?php echo $row['ff'].' '.$row['fl']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        Done
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-green-900"><?php echo date("M d, Y", strtotime($row['date_created'])); ?></span>
                                        <span class="text-xs text-gray-400"><?php echo date("h:i A", strtotime($row['date_created'])); ?></span>
                                        <span class="text-[10px] uppercase font-bold text-green-500 mt-1">
                                            <?php echo $row['year'].' '.(($row['semester']==1)?'1st':'2nd').' Sem'; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="viewDetails(<?php echo $row['id']; ?>)" class="p-2 hover:bg-green-100 rounded-lg text-green-600 transition-all border border-transparent hover:border-green-200" title="View Feedback">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div class="px-6 py-4 border-t border-green-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_counts); ?> of <?php echo $total_counts; ?> entries</p>
                    <div class="flex gap-1">
                        <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&academic_id=<?php echo $academic_id; ?>&class_id=<?php echo $class_id; ?>&search=<?php echo $search; ?>" class="px-3 py-1 rounded-lg border border-green-200 text-green-600 hover:bg-green-50">Prev</a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&academic_id=<?php echo $academic_id; ?>&class_id=<?php echo $class_id; ?>&search=<?php echo $search; ?>" class="px-3 py-1 rounded-lg border <?php echo ($page == $i) ? 'bg-green-950 text-white border-green-950' : 'border-green-200 text-green-600 hover:bg-green-50'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&academic_id=<?php echo $academic_id; ?>&class_id=<?php echo $class_id; ?>&search=<?php echo $search; ?>" class="px-3 py-1 rounded-lg border border-green-200 text-green-600 hover:bg-green-50">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Details Modal -->
    <div id="detailsModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
        <div id="modalOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-green-100 flex items-center justify-between bg-green-50">
                <div>
                    <h3 class="text-xl font-bold text-green-950" id="modalStudentName">Evaluation Details</h3>
                    <p class="text-sm text-green-600" id="modalSubHeader"></p>
                </div>
                <button onclick="closeModal()" class="p-2 hover:bg-green-200 rounded-xl transition-all text-green-900">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="p-8 overflow-y-auto space-y-6" id="modalContent">
                <!-- Content will be loaded via AJAX -->
                <div class="flex items-center justify-center py-20">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-green-200 border-t-green-950"></div>
                </div>
            </div>
            <div class="p-6 border-t border-green-100 bg-gray-50 flex justify-end">
                <button onclick="closeModal()" class="px-6 py-2 bg-green-950 text-white rounded-xl font-bold hover:bg-green-900 transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function viewDetails(id) {
            const modal = document.getElementById('detailsModal');
            const content = document.getElementById('modalContent');
            const studentName = document.getElementById('modalStudentName');
            const subHeader = document.getElementById('modalSubHeader');
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            fetch(`../ajax.php?action=get_evaluation_details&id=${id}`)
            .then(r => r.json())
            .then(res => {
                studentName.textContent = res.student_name;
                subHeader.textContent = `Evaluated Professor: ${res.faculty_name} | ${res.year} ${res.semester==1?'1st':'2nd'} Sem`;
                
                let html = `
                    <div class="space-y-8">
                        <div>
                            <h4 class="text-xs font-black text-green-400 uppercase tracking-widest mb-4">Questions & Ratings</h4>
                            <div class="space-y-4">
                `;
                
                res.answers.forEach(item => {
                    html += `
                        <div class="p-4 rounded-2xl border border-green-100 bg-green-50/30 flex justify-between items-center gap-4">
                            <p class="text-green-800 font-medium">${item.question}</p>
                            <span class="w-10 h-10 flex items-center justify-center rounded-full bg-green-950 text-white font-black shrink-0">
                                ${item.rating}
                            </span>
                        </div>
                    `;
                });
                
                html += `
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-xs font-black text-green-400 uppercase tracking-widest mb-4">Student Comment</h4>
                            <div class="p-6 rounded-2xl bg-green-50 border-2 border-dashed border-green-200 italic text-green-900 leading-relaxed">
                                "${res.comment || 'No comment provided.'}"
                            </div>
                        </div>
                    </div>
                `;
                
                content.innerHTML = html;
            })
            .catch(err => {
                content.innerHTML = `<div class="text-center text-red-500">Error loading details.</div>`;
            });
        }

        function closeModal() {
            document.getElementById('detailsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('modalOverlay').onclick = closeModal;

        function exportCSV() {
            const academic_id = "<?php echo $academic_id; ?>";
            const class_id = "<?php echo $class_id; ?>";
            const search = "<?php echo $search; ?>";
            
            let url = `export_completed_evaluations.php?academic_id=${academic_id}&class_id=${class_id}&search=${search}`;
            window.location.href = url;
        }
    </script>
</body>
</html>
