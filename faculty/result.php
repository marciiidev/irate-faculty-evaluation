<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'faculty') {
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
    <title>Evaluation Result | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: #06402b !important; padding: 0 !important; margin: 0 !important; }
            .print-container { padding: 40px !important; margin: 0 !important; border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; }
            .card { border: 1px solid #e5e7eb !important; border-radius: 12px !important; }
            .progress-bar { border: 1px solid #000 !important; }
            .comments-section { max-height: none !important; overflow: visible !important; }
            main { margin-left: 0 !important; width: 100% !important; }
            .sidebar { display: none !important; }
            .rounded-2xl, .rounded-3xl { border-radius: 12px !important; }
            .shadow-sm, .shadow-lg, .shadow-2xl { box-shadow: none !important; }
            .is-pdf-header { display: flex !important; }
            .is-pdf-title { display: block !important; }
        }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex text-green-950">
    <div class="no-print">
        <?php include 'sidebar.php'; ?>
    </div>
    <main class="flex-1 flex flex-col min-w-0 print-container">
        <div class="no-print">
            <?php include 'topbar.php'; ?>
        </div>
        <div class="max-w-7xl mx-auto w-full md:p-12 p-6">
            <!-- Non-PDF Page Header -->
            <div class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 no-print">
                <div>
                    <h1 class="text-3xl font-black text-green-950 uppercase tracking-tight">Evaluation Results</h1>
                    <p class="text-green-700 text-sm md:text-base font-medium">Performance insights and student feedback analysis.</p>
                </div>
                <div class="w-full md:w-auto">
                    <button onclick="downloadPDF(event)" class="bg-green-950 text-white w-full md:w-auto px-8 py-4 rounded-2xl font-bold hover:bg-green-900 transition-all shadow-xl shadow-green-900/20 flex items-center justify-center gap-3 active:scale-95">
                        <i data-lucide="download" class="w-5 h-5"></i>
                        Download Report (PDF)
                    </button>
                </div>
            </div>

            <div id="reportContent" class="bg-white rounded-[2rem] overflow-hidden shadow-2xl shadow-green-900/5 min-h-screen">
                <!-- Print Letterhead -->
                <div class="is-pdf-header hidden flex-row items-center justify-between mb-12 border-b-4 border-double border-green-900 pb-10 px-8 pt-8">
                    <div class="flex items-center gap-6">
                        <img src="../assets/Bpc logo.png" alt="BPC Logo" class="w-32 h-32 object-contain">
                        <div class="text-left">
                            <h1 class="text-4xl font-black text-green-950 uppercase tracking-tighter leading-none mb-2">Bulacan Polytechnic College</h1>
                            <p class="text-lg font-bold text-green-800 uppercase tracking-[0.2em] mb-2 text-wrap max-w-md">Faculty Evaluation System</p>
                            <p class="text-base text-green-600 italic">Bulihan, City of Malolos, Bulacan</p>
                        </div>
                    </div>
                    <div class="text-right hidden md:block border-l-2 border-green-100 pl-8 h-24 flex flex-col justify-center">
                        <p class="text-xs font-black text-green-300 uppercase tracking-widest mb-1 italic">Document Status</p>
                        <p class="text-sm font-bold text-green-800 uppercase tracking-widest">Official Report</p>
                        <p class="text-[10px] text-green-400">Ver. 2026.1</p>
                    </div>
                </div>

                <div class="is-pdf-title hidden text-center mb-16 px-8">
                    <h2 class="text-4xl font-black text-green-950 uppercase tracking-[0.5em] border-b-8 border-green-900 inline-block pb-4 leading-none">EVALUATION RESULT</h2>
                </div>

            <?php
            $fid = (int)$_SESSION['login_id'];
            $academic = $conn->query("SELECT * FROM academic_list WHERE is_default = 1")->fetch_assoc();
            if (!$academic) {
                // Fallback to latest if no default
                $academic = $conn->query("SELECT * FROM academic_list ORDER BY id DESC LIMIT 1")->fetch_assoc();
            }

            if (!$academic):
            ?>
            <div class="bg-white p-8 rounded-2xl border border-green-200 shadow-sm text-center py-20">
                <i data-lucide="calendar-off" class="w-12 h-12 text-green-300 mx-auto mb-4"></i>
                <p class="text-green-700 font-bold text-lg">No Academic Period Found</p>
                <p class="text-green-500">Wait for the administrator to set an academic period.</p>
            </div>
            <?php else: 
                $academic_id = $academic['id'];

                // Check if published
                $is_published = 0;
                $pub_check = $conn->query("SELECT is_published FROM published_results WHERE faculty_id = $fid AND academic_id = $academic_id");
                if ($pub_check->num_rows > 0) {
                    $is_published = $pub_check->fetch_assoc()['is_published'];
                }

                if (!$is_published):
                ?>
                <div class="bg-white p-8 rounded-2xl border border-green-200 shadow-sm text-center py-20">
                    <i data-lucide="lock" class="w-12 h-12 text-green-300 mx-auto mb-4"></i>
                    <p class="text-green-700 font-bold text-lg">Results Not Yet Published</p>
                    <p class="text-green-500">The administrator has not yet released the evaluation results for this academic period.</p>
                </div>
                <?php else: 

                // Count total evaluations
                $total_evals_query = $conn->query("SELECT count(id) as total FROM evaluation_list WHERE faculty_id = $fid AND academic_id = $academic_id");
                $total_evals = ($total_evals_query) ? $total_evals_query->fetch_assoc()['total'] : 0;
            
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

            <div class="is-pdf-info hidden mb-12 border-l-[16px] border-green-900 bg-green-50/50 p-12 rounded-r-3xl mx-8 shadow-inner">
                <div class="grid grid-cols-12 gap-y-10">
                    <div class="col-span-4 text-xs font-black text-green-600 uppercase tracking-[0.2em] self-center">Faculty Name</div>
                    <div class="col-span-8 text-3xl font-black text-green-950 uppercase border-b-2 border-green-200 pb-2 leading-none"><?php echo $_SESSION['login_name']; ?></div>
                    
                    <div class="col-span-4 text-xs font-black text-green-600 uppercase tracking-[0.2em] self-center">Academic Period</div>
                    <div class="col-span-8 text-xl font-bold text-green-900 border-b-2 border-green-200 pb-2"><?php echo $academic['year'].' '.(($academic['semester'] == 1) ? '1st' : '2nd').' Sem'; ?></div>
                    
                    <div class="col-span-4 text-xs font-black text-green-600 uppercase tracking-[0.2em] self-center">Date Generated</div>
                    <div class="col-span-8 text-lg font-medium text-green-800 italic"><?php echo date('F d, Y h:i A'); ?></div>
                </div>
            </div>

            <div class="no-print mb-12 px-6 md:px-12 pt-12">
                <div class="bg-green-50/50 rounded-3xl p-8 border border-green-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 bg-green-950 rounded-3xl flex items-center justify-center shadow-2xl shadow-green-900/20">
                            <span class="text-white text-3xl font-black"><?php 
                                $name_parts = explode(' ', $_SESSION['login_name']);
                                echo substr($name_parts[0] ?? 'F', 0, 1).substr(end($name_parts) ?? 'N', 0, 1); 
                            ?></span>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-green-400 uppercase tracking-[0.2em] mb-1">Faculty Member</p>
                            <h2 class="text-3xl font-black text-green-950 uppercase tracking-tight"><?php echo $_SESSION['login_name']; ?></h2>
                            <div class="flex items-center gap-3 mt-2">
                                <div class="flex items-center gap-1.5 bg-white px-3 py-1 rounded-full border border-green-100">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-green-600"></i>
                                    <span class="text-[10px] font-bold text-green-800 uppercase tracking-widest"><?php echo $academic['year']; ?></span>
                                </div>
                                <div class="flex items-center gap-1.5 bg-white px-3 py-1 rounded-full border border-green-100">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 text-green-600"></i>
                                    <span class="text-[10px] font-bold text-green-800 uppercase tracking-widest"><?php echo ($academic['semester'] == 1) ? '1st' : '2nd'; ?> Semester</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-12 px-6 md:px-12">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Main Score Card -->
                    <div class="flex-1 bg-green-950 p-8 rounded-3xl text-white shadow-2xl relative overflow-hidden flex flex-col justify-between min-h-[220px]">
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-6 opacity-60">
                                <i data-lucide="award" class="w-5 h-5"></i>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em]">Overall Performance Rating</span>
                            </div>
                            <div class="flex items-end gap-3">
                                <h2 class="text-6xl font-black leading-none"><?php echo $overall_avg; ?></h2>
                                <div class="mb-1">
                                    <?php $desc_parts = explode(' (', $overall_descriptive); ?>
                                    <p class="text-sm font-black uppercase text-green-400 tracking-widest leading-none"><?php echo rtrim($desc_parts[1] ?? 'N/A', ')'); ?></p>
                                    <p class="text-[10px] font-bold opacity-50 uppercase tracking-widest mt-1"><?php echo $desc_parts[0] ?? ''; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="relative z-10 mt-8 pt-6 border-t border-white/10 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar" class="w-4 h-4 opacity-40"></i>
                                <span class="text-[10px] font-bold tracking-widest uppercase opacity-70"><?php echo $academic['year'].' '.(($academic['semester'] == 1) ? '1st' : '2nd').' Sem'; ?></span>
                            </div>
                            <i data-lucide="shield-check" class="w-10 h-10 opacity-10"></i>
                        </div>
                        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-green-400 rounded-full blur-[100px] opacity-10"></div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 gap-6 md:w-[200px]">
                        <div class="bg-white p-8 rounded-3xl border border-green-100 shadow-sm flex flex-col justify-between">
                            <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center mb-6">
                                <i data-lucide="users" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-3xl font-black text-green-950 leading-none mb-1"><?php echo $total_evals; ?></p>
                                <p class="text-[10px] font-black text-green-400 uppercase tracking-widest">Total Evaluators</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($total_evals > 0): ?>
            <div class="mb-8 px-6 md:px-12 flex items-center gap-4">
                <div class="w-1.5 h-8 bg-green-600 rounded-full"></div>
                <h2 class="text-2xl font-black text-green-950 uppercase tracking-tight">Performance Breakdown</h2>
            </div>
            <div class="space-y-4 md:space-y-6 px-6 md:px-12 mb-12">
                <?php
                $crit_res = $conn->query("SELECT * FROM criteria_list ORDER BY order_by ASC");
                while($crit = $crit_res->fetch_assoc()):
                    $crit_id = $crit['id'];
                    $avg_stmt = $conn->prepare("SELECT AVG(ea.rating) as average FROM evaluation_answers ea JOIN question_list q ON ea.question_id = q.id JOIN evaluation_list el ON ea.evaluation_id = el.id WHERE el.faculty_id = ? AND el.academic_id = ? AND q.criteria_id = ?");
                    $avg_stmt->bind_param("iii", $fid, $academic_id, $crit_id);
                    $avg_stmt->execute();
                    $avg_res = $avg_stmt->get_result()->fetch_assoc();
                    $crit_avg = $avg_res['average'] ?? 0;
                    $percent = ($crit_avg / 5) * 100;
                    
                    $descriptive = "N/A";
                    if ($crit_avg >= 4.50) $descriptive = "Always manifested (Outstanding)";
                    else if ($crit_avg >= 3.50) $descriptive = "Often manifested (Very Satisfactory)";
                    else if ($crit_avg >= 2.50) $descriptive = "Sometimes manifested (Satisfactory)";
                    else if ($crit_avg >= 1.50) $descriptive = "Seldom manifested (Fair)";
                    else if ($crit_avg >= 1.00) $descriptive = "Never/Rarely manifested (Poor)";
                ?>
                <div class="bg-white p-6 md:p-8 rounded-2xl md:rounded-3xl border border-green-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex-1 w-full">
                        <h3 class="font-bold text-green-950 text-base md:text-lg"><?php echo $crit['criteria']; ?></h3>
                        <p class="text-[10px] md:text-sm font-bold text-green-600"><?php echo $descriptive; ?></p>
                    </div>
                    <div class="flex items-center gap-4 md:gap-6 w-full md:w-auto">
                        <div class="flex-1 md:w-64 h-2.5 md:h-3 bg-green-100 rounded-full overflow-hidden">
                            <div class="h-full bg-green-950 transition-all duration-1000" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                        <span class="font-black text-green-950 text-lg md:text-xl min-w-[3rem] text-right"><?php echo number_format($crit_avg, 2); ?></span>
                    </div>
                </div>
                <?php endwhile; ?>

                <!-- Comments Section -->
                <div class="mt-16 px-6 md:px-12 pb-16">
                    <div class="bg-green-50/50 rounded-[2.5rem] p-8 md:p-12 border border-green-100 shadow-inner">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-green-600 rounded-2xl flex items-center justify-center shadow-lg shadow-green-900/20">
                                        <i data-lucide="messages-square" class="w-5 h-5 text-white"></i>
                                    </div>
                                    <h2 class="text-3xl font-black text-green-950 uppercase tracking-tight">Student Feedback Analysis</h2>
                                </div>
                                <p class="text-green-600 font-medium text-sm">Qualitative insights extracted from student evaluations.</p>
                            </div>
                            <div class="no-print bg-white px-6 py-2.5 rounded-2xl border border-green-200 shadow-sm flex items-center gap-3 self-end md:self-auto">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                <span class="text-[10px] font-black text-green-800 uppercase tracking-[0.2em]">Confidential Feedback</span>
                            </div>
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
                            if (empty($comment)) return "Positive";
                            
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
                                if (mb_strpos($comment, $word) !== false) $negCount += 1.5;
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
                            
                            if ($posCount >= $negCount) return "Positive";
                            return "Negative";
                        }

                        $com_stmt = $conn->prepare("SELECT ec.comment FROM evaluation_comments ec JOIN evaluation_list el ON ec.evaluation_id = el.id WHERE el.faculty_id = ? AND el.academic_id = ? AND ec.is_published = 1 ORDER BY el.date_created DESC");
                        $com_stmt->bind_param("ii", $fid, $academic_id);
                        $com_stmt->execute();
                        $com_res = $com_stmt->get_result();
                        
                        $pos_comments = [];
                        $neg_comments = [];

                        while($com = $com_res->fetch_assoc()) {
                            $units = splitFeedback($com['comment']);
                            foreach($units as $unit) {
                                $sentiment = analyzeSentiment($unit);
                                if($sentiment == "Positive") $pos_comments[] = $unit;
                                else $neg_comments[] = $unit;
                            }
                        }

                        if(empty($pos_comments) && empty($neg_comments)):
                        ?>
                            <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-green-200 text-green-300">
                                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mb-6">
                                    <i data-lucide="message-square-off" class="w-10 h-10 opacity-40"></i>
                                </div>
                                <p class="text-lg font-bold text-green-800">No Qualitative Feedback Yet</p>
                                <p class="text-green-500 font-medium">Comments will appear here once published by the administrator.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                                <!-- Published Positive Feedback -->
                                <div class="space-y-6">
                                    <div class="flex items-center justify-between px-4">
                                        <div class="flex items-center gap-3 text-green-700">
                                            <i data-lucide="smile" class="w-5 h-5"></i>
                                            <h3 class="font-black text-sm uppercase tracking-[0.2em]">Positive Feedback</h3>
                                        </div>
                                        <div class="h-px flex-1 mx-4 bg-green-200"></div>
                                        <span class="bg-green-600 text-white px-3 py-1 rounded-full text-[10px] font-black"><?php echo count($pos_comments); ?></span>
                                    </div>
                                    <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar pr-2">
                                        <?php if(empty($pos_comments)): ?>
                                            <div class="p-8 text-center text-green-300 italic border border-dashed border-green-100 rounded-3xl bg-green-50/20">No positive feedback shared.</div>
                                        <?php else: foreach($pos_comments as $c): ?>
                                            <div class="group p-6 bg-white rounded-3xl border border-green-100 shadow-sm transition-all hover:shadow-md hover:border-green-300 relative overflow-hidden">
                                                <div class="absolute top-0 left-0 w-1.5 h-full bg-green-600"></div>
                                                <i data-lucide="quote" class="w-6 h-6 text-green-100 absolute top-4 right-4 group-hover:text-green-200 transition-colors"></i>
                                                <p class="text-green-900 leading-relaxed font-medium italic relative z-10">"<?php echo htmlspecialchars($c); ?>"</p>
                                            </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>

                                <!-- Published Constructive Feedback -->
                                <div class="space-y-6">
                                    <div class="flex items-center justify-between px-4">
                                        <div class="flex items-center gap-3 text-amber-700">
                                            <i data-lucide="frown" class="w-5 h-5"></i>
                                            <h3 class="font-black text-sm uppercase tracking-[0.2em]">Constructive Feedback</h3>
                                        </div>
                                        <div class="h-px flex-1 mx-4 bg-amber-200"></div>
                                        <span class="bg-amber-600 text-white px-3 py-1 rounded-full text-[10px] font-black"><?php echo count($neg_comments); ?></span>
                                    </div>
                                    <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar pr-2">
                                        <?php if(empty($neg_comments)): ?>
                                            <div class="p-8 text-center text-amber-300 italic border border-dashed border-amber-100 rounded-3xl bg-amber-50/20">No constructive feedback recorded.</div>
                                        <?php else: foreach($neg_comments as $c): ?>
                                            <div class="group p-6 bg-white rounded-3xl border border-amber-100 shadow-sm transition-all hover:shadow-md hover:border-amber-300 relative overflow-hidden">
                                                <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-600"></div>
                                                <i data-lucide="quote" class="w-6 h-6 text-amber-100 absolute top-4 right-4 group-hover:text-amber-200 transition-colors"></i>
                                                <p class="text-amber-950 leading-relaxed font-medium italic relative z-10">"<?php echo htmlspecialchars($c); ?>"</p>
                                            </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center py-20 bg-green-100 rounded-3xl border-2 border-dashed border-green-200">
                <i data-lucide="alert-circle" class="w-12 h-12 text-green-300 mx-auto mb-4"></i>
                <p class="text-green-700 font-medium">No evaluation data available for the current academic year.</p>
            </div>
            <?php endif; // End of total_evals check ?>
            <?php endif; // End of is_published check ?>
            <?php endif; // End of academic check ?>

            <!-- PDF Footer (Timestamp Only) -->
            <div class="is-pdf-footer hidden mt-20 pt-10 border-t-2 border-green-100 pb-12 px-8">
                <div class="text-center text-[10px] text-green-400 italic">
                    This is a computer-generated report from the BPC Faculty Evaluation System. Generated on <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>

            </div>
        </div>
    </main>
    <script>
        lucide.createIcons();

        async function downloadPDF(event) {
            const { jsPDF } = window.jspdf;
            const element = document.getElementById('reportContent');
            const button = event.currentTarget;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Generating Report...';
            lucide.createIcons();
            button.disabled = true;

            // Prepare elements for capture
            const pdfHeader = element.querySelector('.is-pdf-header');
            const pdfTitle = element.querySelector('.is-pdf-title');
            const pdfInfo = element.querySelector('.is-pdf-info');
            const pdfFooter = element.querySelector('.is-pdf-footer');
            const noPrint = element.querySelectorAll('.no-print');
            
            // Force visibility for capture
            pdfHeader.style.display = 'flex';
            pdfTitle.style.display = 'block';
            pdfInfo.style.display = 'block';
            pdfFooter.style.display = 'block';
            
            pdfHeader.classList.remove('hidden');
            pdfTitle.classList.remove('hidden');
            pdfInfo.classList.remove('hidden');
            pdfFooter.classList.remove('hidden');

            noPrint.forEach(el => {
                el.style.display = 'none';
                el.classList.add('hidden');
            });

            // Add some padding and fixed width for the capture
            const originalPadding = element.style.padding;
            const originalWidth = element.style.width;
            const originalShadow = element.style.boxShadow;
            element.style.padding = '80px';
            element.style.width = '1200px';
            element.style.boxShadow = 'none';

            try {
                const canvas = await html2canvas(element, {
                    scale: 2, // 2 is usually sufficient and more stable for large areas
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    windowWidth: 1200
                });

                const imgData = canvas.toDataURL('image/png', 1.0);
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                
                const imgWidth = pdfWidth;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pdfHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pdfHeight;
                }

                pdf.save(`Evaluation_Report_<?php echo str_replace(' ', '_', $_SESSION['login_name']); ?>.pdf`);
            } catch (error) {
                console.error('PDF Generation Error:', error);
                alert('An error occurred while generating the PDF. Please try again.');
            } finally {
                // Restore elements
                pdfHeader.style.display = '';
                pdfTitle.style.display = '';
                pdfInfo.style.display = '';
                pdfFooter.style.display = '';

                pdfHeader.classList.add('hidden');
                pdfTitle.classList.add('hidden');
                pdfInfo.classList.add('hidden');
                pdfFooter.classList.add('hidden');

                noPrint.forEach(el => {
                    el.style.display = '';
                    el.classList.remove('hidden');
                });
                
                element.style.padding = originalPadding;
                element.style.width = originalWidth;
                element.style.boxShadow = originalShadow;
                
                button.innerHTML = originalText;
                lucide.createIcons();
                button.disabled = false;
            }
        }
    </script>
</body>
</html>
