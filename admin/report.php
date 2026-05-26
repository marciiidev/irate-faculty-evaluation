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
    <title>Faculty Report | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: #06402b !important; padding: 0 !important; margin: 0 !important; }
            main { margin-left: 0 !important; width: 100% !important; }
            .print-container { padding: 40px !important; margin: 0 !important; border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; }
            .rounded-3xl, .rounded-2xl, .rounded-xl { border-radius: 12px !important; }
            .shadow-sm, .shadow-lg, .shadow-2xl { box-shadow: none !important; }
            .bg-green-50 { background-color: white !important; }
            .border { border-color: #e5e7eb !important; }
            .print-header { display: flex !important; }
            .report-title { display: block !important; }
        }
        .print-header { display: none; }
        .report-title { display: none; }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex flex-col lg:flex-row text-green-950">
    <div class="no-print">
        <?php include 'sidebar.php'; ?>
    </div>
    <main class="flex-1 flex flex-col min-w-0">
        <div class="no-print">
            <?php include 'topbar.php'; ?>
        </div>
        <div class="max-w-7xl mx-auto w-full p-4 md:p-12 print-container">
            <div class="mb-8 md:mb-12 flex flex-col lg:flex-row justify-between items-start lg:items-center no-print gap-6 md:gap-8">
                <div>
                    <h1 class="text-3xl font-black text-green-950 uppercase tracking-tight">Faculty Report</h1>
                    <p class="text-green-700 text-sm md:text-base font-medium">Generate and view evaluation results for faculties.</p>
                </div>
                <?php if(isset($_GET['fid'])): ?>
                <button onclick="window.print()" class="w-full md:w-auto bg-green-950 text-white px-8 py-4 rounded-2xl font-bold hover:bg-green-900 transition-all shadow-xl shadow-green-900/20 flex items-center justify-center gap-3">
                    <i data-lucide="printer" class="w-5 h-5"></i>
                    Print / Download PDF
                </button>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-2xl border border-green-200 shadow-sm space-y-6 no-print">
                <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 relative" id="facultySearchContainer">
                        <label class="text-sm font-bold text-green-800">Select Faculty</label>
                        <div class="relative">
                            <input type="text" 
                                   id="facultySearchInput" 
                                   placeholder="Search faculty name..." 
                                   class="w-full px-4 py-3 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none transition-all text-sm md:text-base pr-12"
                                   autocomplete="off"
                                   value="<?php 
                                        if(isset($_GET['fid'])) {
                                            $f_stmt = $conn->prepare("SELECT firstname, lastname FROM faculty_list WHERE id = ?");
                                            $f_stmt->bind_param("i", $_GET['fid']);
                                            $f_stmt->execute();
                                            $f_res = $f_stmt->get_result()->fetch_assoc();
                                            if($f_res) echo htmlspecialchars($f_res['firstname'].' '.$f_res['lastname']);
                                        }
                                   ?>">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                <button type="button" id="clearSearch" class="text-green-300 hover:text-green-600 transition-colors hidden">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                                <i data-lucide="chevron-down" class="w-5 h-5 text-green-400"></i>
                            </div>
                        </div>
                        
                        <input type="hidden" name="fid" id="facultyIdInput" value="<?php echo $_GET['fid'] ?? ''; ?>" required>
                        
                        <div id="facultyDropdown" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-green-200 rounded-2xl shadow-2xl max-h-64 overflow-y-auto hidden custom-scrollbar">
                            <div class="p-2 sticky top-0 bg-white border-b border-green-100 flex items-center gap-2">
                                <i data-lucide="search" class="w-3 h-3 text-green-300 ml-2"></i>
                                <span class="text-[10px] font-bold text-green-300 uppercase tracking-widest">Global Search Active</span>
                            </div>
                            <?php
                            $fac_res = $conn->query("SELECT * FROM faculty_list WHERE is_deleted = 0 ORDER BY lastname ASC");
                            if($fac_res->num_rows > 0):
                                while($fac = $fac_res->fetch_assoc()):
                                    $fullName = $fac['firstname'].' '.$fac['lastname'];
                            ?>
                            <div class="faculty-option px-5 py-3 hover:bg-green-50 cursor-pointer transition-colors border-b border-green-50 last:border-0 group" 
                                 data-id="<?php echo $fac['id']; ?>" 
                                 data-name="<?php echo htmlspecialchars($fullName); ?>"
                                 data-idno="<?php echo htmlspecialchars($fac['school_id'] ?? ''); ?>">
                                <div class="flex flex-col">
                                    <span class="font-bold text-green-950 text-sm group-hover:text-green-700 transition-colors"><?php echo htmlspecialchars($fullName); ?></span>
                                    <span class="text-[10px] text-green-400 uppercase tracking-widest font-black"><?php echo htmlspecialchars($fac['school_id'] ?? 'No ID'); ?></span>
                                </div>
                            </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <div class="p-8 text-center text-green-300 italic">No faculty found</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-green-950 text-white py-3 rounded-xl font-bold hover:bg-green-900 transition-all shadow-lg">
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>

            <?php if(isset($_GET['fid'])): 
                $fid = (int)$_GET['fid'];
                $faculty_query = $conn->query("SELECT * FROM faculty_list WHERE id = $fid");
                $academic = $conn->query("SELECT * FROM academic_list WHERE is_default = 1")->fetch_assoc();
                if (!$academic) {
                    $academic = $conn->query("SELECT * FROM academic_list ORDER BY id DESC LIMIT 1")->fetch_assoc();
                }

                if ($faculty_query->num_rows == 0):
                    echo '<div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 mt-8">Faculty not found.</div>';
                elseif (!$academic):
                    echo '<div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 mt-8">No academic period found. Please set an academic period first.</div>';
                else:
                    $faculty = $faculty_query->fetch_assoc();
                    $academic_id = $academic['id'];

                    // Count total evaluations
                    $total_evals_query = $conn->query("SELECT count(id) as total FROM evaluation_list WHERE faculty_id = $fid AND academic_id = $academic_id");
                    $total_evals = ($total_evals_query) ? $total_evals_query->fetch_assoc()['total'] : 0;

                    // Check if published
                    $is_published = 0;
                    $pub_check = $conn->query("SELECT is_published FROM published_results WHERE faculty_id = $fid AND academic_id = $academic_id");
                    if ($pub_check->num_rows > 0) {
                        $is_published = $pub_check->fetch_assoc()['is_published'];
                    }
                
                // Calculate overall average
                $overall_avg_res = $conn->query("SELECT AVG(ea.rating) as overall_avg FROM evaluation_answers ea JOIN evaluation_list el ON ea.evaluation_id = el.id WHERE el.faculty_id = $fid AND el.academic_id = $academic_id")->fetch_assoc();
                $overall_avg = number_format($overall_avg_res['overall_avg'] ?? 0, 2);

                $overall_descriptive = "N/A";
                if ($overall_avg >= 4.50) $overall_descriptive = "Always manifested (Outstanding)";
                else if ($overall_avg >= 3.50) $overall_descriptive = "Often manifested (Very Satisfactory)";
                else if ($overall_avg >= 2.50) $overall_descriptive = "Sometimes manifested (Satisfactory)";
                else if ($overall_avg >= 1.50) $overall_descriptive = "Seldom manifested (Fair)";
                else if ($overall_avg >= 1.00) $overall_descriptive = "Never/Rarely manifested (Poor)";
            ?>
            <div class="mt-8 space-y-8" id="reportContent">
                <!-- Print Letterhead -->
                <div class="print-header hidden flex-row items-center justify-center gap-6 mb-10 border-b-2 border-green-900 pb-6">
                    <img src="../assets/Bpc logo.png" alt="BPC Logo" class="w-24 h-24 object-contain">
                    <div class="text-center">
                        <h1 class="text-2xl font-black text-green-950 uppercase tracking-tighter">Bulacan Polytechnic College</h1>
                        <p class="text-sm font-bold text-green-800 uppercase tracking-widest">Faculty Evaluation System</p>
                        <p class="text-xs text-green-600 mt-1">Bulihan, City of Malolos, Bulacan</p>
                    </div>
                </div>

                <div class="report-title text-center mb-8 px-6 md:px-12">
                    <h2 class="text-xl font-black text-green-950 uppercase tracking-widest underline decoration-2 underline-offset-8">Faculty Report</h2>
                </div>

                <div class="bg-white p-6 md:p-8 rounded-2xl md:rounded-3xl border border-green-200 shadow-sm flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mx-6 md:mx-12">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl md:rounded-3xl bg-green-950 flex items-center justify-center shrink-0 shadow-2xl shadow-green-900/20">
                            <span class="text-white text-xl md:text-2xl font-black"><?php echo substr($faculty['firstname'], 0, 1).substr($faculty['lastname'], 0, 1); ?></span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-green-400 tracking-widest mb-1">Faculty Member</p>
                            <h3 class="text-xl md:text-2xl font-black text-green-950 uppercase tracking-tight"><?php echo $faculty['firstname'].' '.$faculty['lastname']; ?></h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs font-black text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-100 uppercase tracking-widest"><?php echo htmlspecialchars($faculty['school_id']); ?></span>
                                <span class="text-[10px] font-bold text-green-700 bg-green-100/50 px-2 py-0.5 rounded uppercase tracking-widest"><?php echo $academic['year'].' '.(($academic['semester'] == 1) ? '1st' : '2nd').' Sem'; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col md:flex-row items-center gap-4 w-full lg:w-auto">
                        <?php
                        // Participation Logic: Students who were supposed to evaluate vs those who did
                        $assigned_students_res = $conn->query("SELECT SUM(student_count) as total FROM (
                            SELECT count(s.id) as student_count 
                            FROM restriction_list r 
                            JOIN student_list s ON r.class_id = s.class_id 
                            WHERE r.faculty_id = $fid AND r.academic_id = $academic_id AND r.is_deleted = 0
                            GROUP BY r.class_id
                        ) as subquery");
                        $assigned_count = $assigned_students_res->fetch_assoc()['total'] ?? 0;
                        $participation_rate = ($assigned_count > 0) ? round(($total_evals / $assigned_count) * 100, 1) : 0;
                        $participation_color = ($participation_rate >= 80) ? 'text-green-600' : (($participation_rate >= 50) ? 'text-amber-600' : 'text-red-600');
                        ?>
                        
                        <div class="flex-1 lg:w-48 bg-white border border-green-100 px-6 py-4 rounded-2xl text-center relative group min-w-[160px]">
                            <p class="text-[10px] uppercase font-bold text-green-400 tracking-widest mb-1">Participation</p>
                            <div class="flex items-center justify-center gap-2">
                                <p class="text-xl font-black text-green-950 leading-none"><?php echo $total_evals; ?></p>
                                <span class="text-xs text-gray-300 font-bold">/ <?php echo $assigned_count; ?></span>
                            </div>
                            <div class="mt-2 w-full bg-gray-50 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-green-600 h-full rounded-full transition-all duration-1000" style="width: <?php echo $participation_rate; ?>%"></div>
                            </div>
                            <p class="text-[8px] font-black uppercase mt-1 <?php echo $participation_color; ?>"><?php echo $participation_rate; ?>% Completion</p>
                        </div>

                        <div class="flex-1 lg:w-48 bg-green-950 text-white px-6 py-4 rounded-2xl text-center shadow-xl min-w-[160px]">
                            <p class="text-[10px] uppercase font-bold opacity-70 tracking-widest mb-1">Overall Rating</p>
                            <p class="text-2xl font-black leading-none"><?php echo $overall_avg; ?></p>
                            <p class="text-[10px] font-bold uppercase mt-1 text-green-400 leading-none"><?php echo explode(' (', $overall_descriptive)[1] ?? ''; ?></p>
                        </div>

                        <div class="no-print w-full md:w-auto">
                            <button id="togglePublication" 
                                    data-fid="<?php echo $fid; ?>" 
                                    data-acad="<?php echo $academic_id; ?>" 
                                    data-status="<?php echo $is_published; ?>"
                                    class="h-full py-4 px-6 rounded-2xl font-bold transition-all flex flex-col items-center justify-center gap-1 min-w-[140px] <?php echo $is_published ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-100'; ?>">
                                <i data-lucide="<?php echo $is_published ? 'eye-off' : 'send'; ?>" class="w-5 h-5"></i>
                                <span class="text-[10px] uppercase tracking-widest"><?php echo $is_published ? 'Unpublish' : 'Publish'; ?></span>
                            </button>
                        </div>
                    </div>
                </div>

                <?php if($total_evals > 0): ?>
                


                <!-- Summary Table -->
                <div class="bg-white rounded-2xl md:rounded-3xl border border-green-200 shadow-sm overflow-hidden mx-6 md:mx-12">
                    <div class="bg-green-50 px-6 md:px-8 py-6 md:py-8 border-b border-green-200">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div>
                                <h3 class="font-black text-green-950 text-xl md:text-2xl uppercase tracking-tight mb-2">Evaluation Summary</h3>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-bold text-green-400 uppercase tracking-widest">Academic Year:</span>
                                        <span class="text-xs font-bold text-green-800"><?php echo $academic['year']; ?></span>
                                    </div>
                                    <div class="w-1 h-1 bg-green-200 rounded-full hidden md:block"></div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-bold text-green-400 uppercase tracking-widest">Semester:</span>
                                        <span class="text-xs font-bold text-green-800"><?php echo ($academic['semester'] == 1) ? '1st' : '2nd'; ?> Semester</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white border border-green-100 p-4 rounded-2xl flex items-center gap-4 shadow-inner">
                                <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                                    <i data-lucide="users" class="w-5 h-5 text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-green-400 uppercase tracking-widest leading-none mb-1">Total Evaluators</p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-lg font-black text-green-950 leading-none"><?php echo $total_evals; ?></p>
                                        <?php if($participation_rate >= 100): ?>
                                        <span class="bg-green-600 text-white text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-tighter">everyone has evaluated it</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-green-50/30">
                                    <th class="px-6 md:px-8 py-4 text-[10px] md:text-xs font-bold text-green-700 uppercase tracking-widest">Criteria</th>
                                    <th class="px-6 md:px-8 py-4 text-[10px] md:text-xs font-bold text-green-700 uppercase tracking-widest text-center">Average</th>
                                    <th class="px-6 md:px-8 py-4 text-[10px] md:text-xs font-bold text-green-700 uppercase tracking-widest">Interpretation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $criteria_res = $conn->query("SELECT * FROM criteria_list ORDER BY order_by ASC");
                                while($crit = $criteria_res->fetch_assoc()):
                                    $crit_id = $crit['id'];
                                    $avg_stmt = $conn->prepare("SELECT AVG(ea.rating) as average FROM evaluation_answers ea JOIN question_list q ON ea.question_id = q.id JOIN evaluation_list el ON ea.evaluation_id = el.id WHERE el.faculty_id = ? AND el.academic_id = ? AND q.criteria_id = ?");
                                    $avg_stmt->bind_param("iii", $fid, $academic_id, $crit_id);
                                    $avg_stmt->execute();
                                    $avg_res = $avg_stmt->get_result()->fetch_assoc();
                                    $crit_avg = number_format($avg_res['average'] ?? 0, 2);
                                    
                                    $interp = "N/A";
                                    if ($crit_avg >= 4.50) $interp = "Always (Outstanding)";
                                    else if ($crit_avg >= 3.50) $interp = "Often (Very Satisfactory)";
                                    else if ($crit_avg >= 2.50) $interp = "Sometimes (Satisfactory)";
                                    else if ($crit_avg >= 1.50) $interp = "Seldom (Fair)";
                                    else if ($crit_avg >= 1.00) $interp = "Never (Poor)";
                                ?>
                                <tr class="border-b border-green-100 hover:bg-green-50 transition-all">
                                    <td class="px-6 md:px-8 py-4 font-bold text-green-950 text-sm md:text-base"><?php echo $crit['criteria']; ?></td>
                                    <td class="px-6 md:px-8 py-4 text-center font-black text-green-900 text-sm md:text-base"><?php echo $crit_avg; ?></td>
                                    <td class="px-6 md:px-8 py-4 text-xs md:text-sm font-bold text-green-600"><?php echo $interp; ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <tr class="bg-green-950 text-white">
                                    <td class="px-6 md:px-8 py-6 font-black uppercase tracking-widest text-sm md:text-base">Total Average</td>
                                    <td class="px-6 md:px-8 py-6 text-center font-black text-xl md:text-2xl"><?php echo $overall_avg; ?></td>
                                    <td class="px-6 md:px-8 py-6 font-black uppercase tracking-widest text-xs md:text-sm"><?php echo explode(' (', $overall_descriptive)[1] ?? $overall_descriptive; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-4 px-6 md:px-12">
                    <div class="w-1.5 h-8 bg-green-600 rounded-full"></div>
                    <h2 class="text-2xl font-black text-green-950 uppercase tracking-tight">Performance Breakdown</h2>
                </div>

                <div class="grid grid-cols-1 gap-8 px-6 md:px-12">
                    <?php
                    $criteria_res = $conn->query("SELECT * FROM criteria_list ORDER BY order_by ASC");
                    while($crit = $criteria_res->fetch_assoc()):
                        $crit_id = $crit['id'];
                        $avg_stmt = $conn->prepare("SELECT AVG(ea.rating) as average FROM evaluation_answers ea JOIN question_list q ON ea.question_id = q.id JOIN evaluation_list el ON ea.evaluation_id = el.id WHERE el.faculty_id = ? AND el.academic_id = ? AND q.criteria_id = ?");
                        $avg_stmt->bind_param("iii", $fid, $academic_id, $crit_id);
                        $avg_stmt->execute();
                        $avg_res = $avg_stmt->get_result()->fetch_assoc();
                        $crit_avg = number_format($avg_res['average'] ?? 0, 2);
                        
                        $descriptive = "N/A";
                        if ($crit_avg >= 4.50) $descriptive = "Always manifested (Outstanding)";
                        else if ($crit_avg >= 3.50) $descriptive = "Often manifested (Very Satisfactory)";
                        else if ($crit_avg >= 2.50) $descriptive = "Sometimes manifested (Satisfactory)";
                        else if ($crit_avg >= 1.50) $descriptive = "Seldom manifested (Fair)";
                        else if ($crit_avg >= 1.00) $descriptive = "Never/Rarely manifested (Poor)";
                    ?>
                    <div class="bg-white rounded-2xl md:rounded-3xl border border-green-200 shadow-sm overflow-hidden break-inside-avoid">
                        <div class="bg-green-50 px-6 md:px-8 py-4 md:py-6 border-b border-green-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <h3 class="font-bold text-green-950 text-base md:text-lg"><?php echo $crit['criteria']; ?></h3>
                                <p class="text-[10px] md:text-sm font-bold text-green-600"><?php echo $descriptive; ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] md:text-sm font-bold text-green-700 uppercase">Average:</span>
                                <span class="text-lg md:text-xl font-black text-green-950"><?php echo $crit_avg; ?></span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[500px]">
                                <thead>
                                    <tr class="bg-green-50/50">
                                        <th class="px-6 md:px-8 py-3 text-[10px] md:text-xs font-bold text-green-700 uppercase tracking-widest">Question</th>
                                        <th class="px-6 md:px-8 py-3 text-[10px] md:text-xs font-bold text-green-700 uppercase tracking-widest text-right">Average</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q_stmt = $conn->prepare("SELECT q.id, q.question, AVG(ea.rating) as q_avg FROM question_list q LEFT JOIN evaluation_answers ea ON q.id = ea.question_id LEFT JOIN evaluation_list el ON ea.evaluation_id = el.id WHERE q.criteria_id = ? AND q.academic_id = ? AND (el.faculty_id = ? OR el.faculty_id IS NULL) GROUP BY q.id ORDER BY q.order_by ASC");
                                    $q_stmt->bind_param("iii", $crit_id, $academic_id, $fid);
                                    $q_stmt->execute();
                                    $q_res = $q_stmt->get_result();
                                    while($q = $q_res->fetch_assoc()):
                                    ?>
                                    <tr class="border-b border-green-100 last:border-0 hover:bg-green-50 transition-all">
                                        <td class="px-6 md:px-8 py-4 text-xs md:text-sm text-green-800"><?php echo $q['question']; ?></td>
                                        <td class="px-6 md:px-8 py-4 text-right">
                                            <span class="font-bold text-green-950 text-sm md:text-base"><?php echo number_format($q['q_avg'] ?? 0, 2); ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <!-- Comments Section -->
                    <div class="mt-12 break-inside-avoid">
                        <div class="flex items-center gap-4 mb-6">
                            <i data-lucide="messages-square" class="w-8 h-8 text-green-600"></i>
                            <h2 class="text-2xl font-black text-green-950 uppercase tracking-tight">Student Feedback Analysis</h2>
                        </div>

                        <?php
                        function splitFeedback($text) {
                            if (empty($text)) return [];
                            // Only split on strong conjunctions that indicate a shift in sentiment
                            $pattern = '/(\s+but\s+)|(\s+pero\s+)|(\s+subalit\s+)|(\s+gayunpaman\s+)|(\s+however\s+)|(\s+although\s+)|(\s+kahit\s+)/i';
                            $parts = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                            
                            $units = [];
                            $current = '';
                            foreach ($parts as $p) {
                                if (preg_match($pattern, $p)) {
                                    $trimmed = trim($current);
                                    if (strlen($trimmed) > 2) $units[] = $trimmed;
                                    $current = '';
                                } else {
                                    $current .= $p;
                                }
                            }
                            $trimmed = trim($current);
                            if (strlen($trimmed) > 2) $units[] = $trimmed;
                            
                            return empty($units) ? [trim($text)] : $units;
                        }

                        function analyzeSentiment($comment) {
                            if (empty($comment)) return ["Positive", "bg-green-100 text-green-700 border-green-200"];
                            
                            $positives = [
                                'good', 'excellent', 'great', 'awesome', 'nice', 'best', 'helpful', 'wonderful', 'effective', 'amazing', 'well', 'clear', 'organized', 'passionate', 'kind', 'approachable', 'fair', 'superb', 'fast', 'brilliant', 'patient', 'understanding', 'efficient', 'inspiring', 'knowledgeable', 'teaching', 'learn', 'learned', 'friendly', 'active', 'dedicated', 'always', 'consistent', 'love', 'liked', 'perfect', 'satisfied', 'thanks', 'thank', 'salute', 'master', 'idol',
                                'magaling', 'mahusay', 'mabait', 'matulungin', 'maayos', 'madaling lapitan', 'masaya', 'energetic', 'sipag', 'masipag', 'maganda', 'paborito', 'lodi', 'sulit', 'dabest', 'petmalu', 'hanga', 'bilis', 'linaw', 'unawa', 'nagtuturo', 'malinaw', 'marami', 'natutunan', 'matalino', 'handa', 'laging', 'palaging', 'natuto', 'salamat', 'nagpapasalamat', 'asahan'
                            ];
                            $negatives = [
                                'bad', 'poor', 'terrible', 'worst', 'unhelpful', 'boring', 'confused', 'lazy', 'difficult', 'late', 'disorganized', 'strict', 'rude', 'unfair', 'unprofessional', 'mean', 'slow', 'loud', 'angry', 'bias', 'absent', 'missing', 'failed', 'fail', 'waste', 'useless', 'improve', 'needs', 'lack', 'shortage',
                                'masungit', 'tamad', 'mabagal', 'mahirap intindihin', 'nakakaantok', 'galit', 'mura', 'pangit', 'laging wala', 'wala kaming matutunan', 'magulo', 'bias', 'unfair', 'kupad', 'tulog', 'late', 'absent', 'hindi nagtuturo', 'di nagtuturo', 'kulang', 'wala', 'sayang'
                            ];
                            
                            $comment = mb_strtolower($comment);
                            $posCount = 0;
                            $negCount = 0;
                            $negators = ['not', 'hindi', 'di', 'no', 'never', 'wala', 'kulang', 'wag', 'huwag'];
                            
                            foreach ($negatives as $word) {
                                if (mb_strpos($comment, $word) !== false) {
                                    $negCount += 1.5;
                                }
                            }
                            
                            foreach ($positives as $word) {
                                if (mb_strpos($comment, $word) !== false) {
                                    $isNegated = false;
                                    foreach ($negators as $neg) {
                                        if (mb_strpos($comment, $neg . ' ' . $word) !== false || mb_strpos($comment, $neg . $word) !== false) {
                                            $isNegated = true;
                                            break;
                                        }
                                    }
                                    if ($isNegated) $negCount += 1.5;
                                    else $posCount++;
                                }
                            }
                            
                            if ($posCount >= $negCount) return ["Positive", "bg-green-100 text-green-700 border-green-200"];
                            return ["Negative", "bg-red-100 text-red-700 border-red-200"];
                        }

                        $com_stmt = $conn->prepare("SELECT ec.id, ec.comment, ec.is_published FROM evaluation_comments ec JOIN evaluation_list el ON ec.evaluation_id = el.id WHERE el.faculty_id = ? AND el.academic_id = ?");
                        $com_stmt->bind_param("ii", $fid, $academic_id);
                        $com_stmt->execute();
                        $com_res = $com_stmt->get_result();
                        
                        $pos_comments = [];
                        $neg_comments = [];

                        while($com = $com_res->fetch_assoc()) {
                            $units = splitFeedback($com['comment']);
                            foreach($units as $unit) {
                                $res = analyzeSentiment($unit);
                                $unit_com = $com;
                                $unit_com['comment'] = $unit;
                                $unit_com['sentiment'] = $res[0];
                                $unit_com['sentiment_class'] = $res[1];
                                if($res[0] == "Positive") $pos_comments[] = $unit_com;
                                else $neg_comments[] = $unit_com;
                            }
                        }
                        ?>


                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Positive Feedback Section -->
                            <div class="bg-white rounded-3xl border-2 border-green-200 overflow-hidden shadow-lg shadow-green-900/5">
                                <div class="bg-green-600 px-6 py-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-white">
                                        <i data-lucide="smile" class="w-5 h-5"></i>
                                        <h3 class="font-black uppercase tracking-tight text-sm">Positive Feedback</h3>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer no-print group">
                                            <span class="text-[10px] font-black uppercase text-white/70 group-hover:text-white transition-colors">Select All</span>
                                            <input type="checkbox" class="sr-only peer select-all-feedback" data-target="positive">
                                            <div class="w-8 h-4 bg-white/20 rounded-full relative after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-white peer-checked:after:translate-x-4"></div>
                                        </label>
                                        <span class="bg-white text-green-600 px-2 py-0.5 rounded-full text-xs font-black"><?php echo count($pos_comments); ?></span>
                                    </div>
                                </div>
                                <div class="p-6 space-y-4 max-h-[500px] overflow-y-auto custom-scrollbar feedback-container" data-type="positive">
                                    <?php if(empty($pos_comments)): ?>
                                        <p class="text-green-300 italic text-center py-8">No positive comments detected.</p>
                                    <?php else: foreach($pos_comments as $c): ?>
                                        <div class="p-4 bg-green-50 rounded-2xl border border-green-100 relative group transition-all hover:shadow-md">
                                            <p class="text-green-900 text-sm font-medium italic pr-12">"<?php echo $c['comment']; ?>"</p>
                                            <div class="absolute top-4 right-4 no-print">
                                                <input type="checkbox" class="sr-only peer comment-toggle" data-id="<?php echo $c['id']; ?>" <?php echo $c['is_published'] ? 'checked' : ''; ?>>
                                                <div onclick="this.previousElementSibling.click()" class="w-8 h-4 bg-gray-200 rounded-full cursor-pointer relative after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-green-600 peer-checked:after:translate-x-4"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>

                        <!-- Constructive/Negative Feedback Section -->
                        <div class="bg-white rounded-3xl border-2 border-red-100 overflow-hidden shadow-lg shadow-red-900/5">
                            <div class="bg-red-600 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3 text-white">
                                    <i data-lucide="frown" class="w-5 h-5"></i>
                                    <h3 class="font-black uppercase tracking-tight text-sm">Constructive Feedback</h3>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer no-print group">
                                        <span class="text-[10px] font-black uppercase text-white/70 group-hover:text-white transition-colors">Select All</span>
                                        <input type="checkbox" class="sr-only peer select-all-feedback" data-target="constructive">
                                        <div class="w-8 h-4 bg-white/20 rounded-full relative after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-white peer-checked:after:translate-x-4"></div>
                                    </label>
                                    <span class="bg-white text-red-600 px-2 py-0.5 rounded-full text-xs font-black"><?php echo count($neg_comments); ?></span>
                                </div>
                            </div>
                            <div class="p-6 space-y-4 max-h-[500px] overflow-y-auto custom-scrollbar feedback-container" data-type="constructive">
                                <?php if(empty($neg_comments)): ?>
                                    <p class="text-red-200 italic text-center py-8">No constructive feedback detected.</p>
                                <?php else: foreach($neg_comments as $c): ?>
                                    <div class="p-4 bg-red-50 rounded-2xl border border-red-100 relative group transition-all hover:shadow-md">
                                        <p class="text-red-900 text-sm font-medium italic pr-12">"<?php echo $c['comment']; ?>"</p>
                                        <div class="absolute top-4 right-4 no-print text-right">
                                            <input type="checkbox" class="sr-only peer comment-toggle" data-id="<?php echo $c['id']; ?>" <?php echo $c['is_published'] ? 'checked' : ''; ?>>
                                            <div onclick="this.previousElementSibling.click()" class="w-8 h-4 bg-gray-200 rounded-full cursor-pointer relative after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-green-600 peer-checked:after:translate-x-4 ml-auto"></div>
                                            <p class="text-[8px] font-bold text-gray-400 mt-1 uppercase"><?php echo $c['is_published'] ? 'Visible' : 'Hidden'; ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-20 bg-green-100 rounded-3xl border-2 border-dashed border-green-200">
                    <i data-lucide="alert-circle" class="w-12 h-12 text-green-300 mx-auto mb-4"></i>
                    <p class="text-green-400 font-medium">No evaluations found for this faculty in the current academic year.</p>
                </div>
                <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
            <div class="mt-12 text-center py-20 bg-green-100 rounded-3xl border-2 border-dashed border-green-200 no-print">
                <i data-lucide="file-text" class="w-12 h-12 text-green-300 mx-auto mb-4"></i>
                <p class="text-green-400 font-medium">Select a faculty member to generate the report.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        lucide.createIcons();

        // Searchable Dropdown Logic
        const searchInput = document.getElementById('facultySearchInput');
        const dropdown = document.getElementById('facultyDropdown');
        const options = document.querySelectorAll('.faculty-option');
        const hiddenId = document.getElementById('facultyIdInput');
        const clearBtn = document.getElementById('clearSearch');

        if(searchInput.value) clearBtn.classList.remove('hidden');

        searchInput.addEventListener('focus', () => {
            dropdown.classList.remove('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!document.getElementById('facultySearchContainer').contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            let hasVisible = false;
            
            options.forEach(opt => {
                const name = opt.dataset.name.toLowerCase();
                const idno = opt.dataset.idno.toLowerCase();
                if (name.includes(val) || idno.includes(val)) {
                    opt.style.display = 'block';
                    hasVisible = true;
                } else {
                    opt.style.display = 'none';
                }
            });

            if(this.value) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
                hiddenId.value = '';
            }

            dropdown.classList.remove('hidden');
        });

        options.forEach(opt => {
            opt.addEventListener('click', function() {
                searchInput.value = this.dataset.name;
                hiddenId.value = this.dataset.id;
                dropdown.classList.add('hidden');
                clearBtn.classList.remove('hidden');
            });
        });

        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            hiddenId.value = '';
            clearBtn.classList.add('hidden');
            searchInput.focus();
            options.forEach(opt => opt.style.display = 'block');
        });

        document.getElementById('togglePublication')?.addEventListener('click', function() {
            const btn = this;
            const fid = btn.dataset.fid;
            const acad = btn.dataset.acad;
            const status = parseInt(btn.dataset.status);
            const newStatus = status === 1 ? 0 : 1;

            fetch('../ajax.php?action=toggle_report_publish', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `fid=${fid}&academic_id=${acad}&publish=${newStatus}`
            })
            .then(r => r.text())
            .then(res => {
                if(res == 1) {
                    location.reload();
                } else {
                    alert(res);
                }
            });
        });

        document.querySelectorAll('.comment-toggle').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const id = this.dataset.id;
                const newStatus = this.checked ? 1 : 0;
                const parent = this.closest('.relative');
                const label = parent.querySelector('p.text-\\[8px\\]');
                
                if(label) label.textContent = newStatus ? 'Visible' : 'Hidden';

                fetch('../ajax.php?action=toggle_comment_visibility', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&publish=${newStatus}`
                })
                .then(r => r.text())
                .then(res => {
                    if(res != 1) {
                        alert(res);
                        this.checked = !this.checked; // Revert
                        if(label) label.textContent = !newStatus ? 'Visible' : 'Hidden';
                    }
                });
            });
        });

        document.querySelectorAll('.select-all-feedback').forEach(masterToggle => {
            masterToggle.addEventListener('change', function() {
                const target = this.dataset.target;
                const newStatus = this.checked ? 1 : 0;
                const container = document.querySelector(`.feedback-container[data-type="${target}"]`);
                const toggles = container.querySelectorAll('.comment-toggle');
                
                const ids = Array.from(toggles).map(t => t.dataset.id);
                
                if (ids.length === 0) return;

                // Optimistically update UI
                toggles.forEach(t => {
                    t.checked = this.checked;
                    const label = t.closest('.relative').querySelector('p.text-\\[8px\\]');
                    if(label) label.textContent = this.checked ? 'Visible' : 'Hidden';
                });

                fetch('../ajax.php?action=batch_toggle_comment_visibility', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ids=${JSON.stringify(ids)}&publish=${newStatus}`
                })
                .then(r => r.text())
                .then(res => {
                    if(res != 1) {
                        alert(res);
                        location.reload(); // Safer to reload on batch failure
                    }
                });
            });
        });


    </script>
</body>
</html>
