<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

// Feedback Processing Helpers
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

// Filters
$f_year = isset($_GET['year']) ? $_GET['year'] : '';
$f_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$f_faculty = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : 0;

// ONLY SHOW CLOSED/ARCHIVED SEMESTERS (status = 2)
$where = " WHERE a.status = 2 ";
if (!empty($f_year)) {
    $where .= " AND a.year = '" . $conn->real_escape_string($f_year) . "' ";
}
if ($f_semester > 0) {
    $where .= " AND a.semester = $f_semester ";
}
if ($f_faculty > 0) {
    $where .= " AND el.faculty_id = $f_faculty ";
}

// Get Master Records Query
$query = "SELECT 
            el.faculty_id, 
            el.academic_id, 
            f.firstname, 
            f.lastname, 
            f.school_id,
            a.year, 
            a.semester, 
            a.status as academic_status,
            COUNT(DISTINCT el.id) as total_students
          FROM evaluation_list el
          JOIN faculty_list f ON el.faculty_id = f.id
          JOIN academic_list a ON el.academic_id = a.id
          $where
          GROUP BY el.faculty_id, el.academic_id
          ORDER BY a.year DESC, a.semester DESC, f.lastname ASC";

$records_res = $conn->query($query);
$records = [];
while($row = $records_res->fetch_assoc()) {
    $fid = $row['faculty_id'];
    $aid = $row['academic_id'];
    
    // Calculate Overall Rating
    $rating_res = $conn->query("SELECT AVG(ea.rating) as avg_rating FROM evaluation_answers ea JOIN evaluation_list el ON ea.evaluation_id = el.id WHERE el.faculty_id = $fid AND el.academic_id = $aid")->fetch_assoc();
    $row['overall_rating'] = number_format($rating_res['avg_rating'] ?? 0, 2);
    
    // Calculate Descriptive
    $rating = $row['overall_rating'];
    if ($rating >= 4.50) $row['descriptive'] = "Outstanding";
    else if ($rating >= 3.50) $row['descriptive'] = "Very Satisfactory";
    else if ($rating >= 2.50) $row['descriptive'] = "Satisfactory";
    else if ($rating >= 1.50) $row['descriptive'] = "Fair";
    else if ($rating >= 1.00) $row['descriptive'] = "Poor";
    else $row['descriptive'] = "N/A";

    // Analyze Remarks
    $comments_res = $conn->query("SELECT ec.comment FROM evaluation_comments ec JOIN evaluation_list el ON ec.evaluation_id = el.id WHERE el.faculty_id = $fid AND el.academic_id = $aid");
    $pos = 0; $neg = 0; 
    while($c = $comments_res->fetch_assoc()) {
        $units = splitFeedback($c['comment']);
        foreach($units as $unit) {
            $sentiment = analyzeSentiment($unit);
            if ($sentiment == "Positive") $pos++;
            else $neg++;
        }
    }
    $row['pos_remarks'] = $pos;
    $row['neg_remarks'] = $neg;
    
    $records[] = $row;
}

// Calculate Overall Analytics
$total_avg = 0;
$total_resp = 0;
if (!empty($records)) {
    foreach($records as $r) {
        $total_avg += (float)$r['overall_rating'];
        $total_resp += (int)$r['total_students'];
    }
    $overall_dept_avg = number_format($total_avg / count($records), 2);
} else {
    $overall_dept_avg = "0.00";
}

// RANKING LOGIC: Sort by Rating Descending
usort($records, function($a, $b) {
    $rA = (float)$a['overall_rating'];
    $rB = (float)$b['overall_rating'];
    if ($rA == $rB) return 0;
    return ($rA > $rB) ? -1 : 1;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Archives | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-emerald-50 min-h-screen flex flex-col lg:flex-row text-emerald-950">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        
        <div class="flex-1 p-4 md:p-8 lg:p-12 overflow-y-auto">
            <div class="max-w-7xl mx-auto w-full">
                <div class="mb-8 md:mb-12 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 md:gap-8">
                    <div>
                        <h1 class="text-4xl font-black text-emerald-950 uppercase tracking-tight mb-2">Academic Archives</h1>
                        <p class="text-emerald-700 font-medium max-w-2xl">Automatic Archiving. All closed semesters and their historical evaluation records are stored here for reference.</p>
                    </div>
                    <div class="flex gap-4 w-full lg:w-auto">
                        <button onclick="exportArchive()" class="flex-1 lg:flex-none bg-emerald-950 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-emerald-900 transition-all shadow-xl shadow-emerald-900/20 flex items-center justify-center gap-3 active:scale-95">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            Export Archives
                        </button>
                    </div>
                </div>

                <!-- Dashboard Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-8 mb-8 md:mb-12">
                    <div class="bg-white p-8 rounded-[2rem] border-2 border-emerald-100 shadow-xl shadow-emerald-900/5 flex flex-col justify-between group hover:border-emerald-500 transition-all duration-300">
                        <div class="flex items-center justify-between mb-8">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="archive" class="w-7 h-7 text-emerald-600"></i>
                            </div>
                            <span class="text-[10px] font-black text-emerald-300 uppercase tracking-widest">Database</span>
                        </div>
                        <div>
                            <p class="text-4xl font-black text-emerald-950 mb-1"><?php echo count($records); ?></p>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Total Archived Records</p>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2rem] border-2 border-emerald-100 shadow-xl shadow-emerald-900/5 flex flex-col justify-between group hover:border-emerald-500 transition-all duration-300">
                        <div class="flex items-center justify-between mb-8">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="trending-up" class="w-7 h-7 text-blue-600"></i>
                            </div>
                            <span class="text-[10px] font-black text-blue-300 uppercase tracking-widest">Performance</span>
                        </div>
                        <div>
                            <p class="text-4xl font-black text-emerald-950 mb-1"><?php echo $overall_dept_avg; ?></p>
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">Global Archive Avg</p>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2rem] border-2 border-emerald-100 shadow-xl shadow-emerald-900/5 flex flex-col justify-between group hover:border-emerald-500 transition-all duration-300">
                        <div class="flex items-center justify-between mb-8">
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="users" class="w-7 h-7 text-amber-600"></i>
                            </div>
                            <span class="text-[10px] font-black text-amber-300 uppercase tracking-widest">Engagement</span>
                        </div>
                        <div>
                            <p class="text-4xl font-black text-emerald-950 mb-1"><?php echo number_format($total_resp); ?></p>
                            <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest">Total Respondents</p>
                        </div>
                    </div>

                    <div class="bg-emerald-950 p-8 rounded-[2rem] text-white shadow-2xl shadow-emerald-900/20 relative overflow-hidden group">
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-6 opacity-60">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                                <h3 class="font-black uppercase tracking-widest text-[10px]">Security</h3>
                            </div>
                            <p class="text-lg font-bold leading-tight mb-2 text-emerald-100">Records Locked</p>
                            <p class="text-[9px] font-black uppercase tracking-widest opacity-40">Ready for Audit</p>
                        </div>
                        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-600 rounded-full blur-[50px] opacity-20 group-hover:scale-150 transition-transform duration-700"></div>
                    </div>
                </div>

                <!-- Analytics Leaderboard -->
                <?php if(!empty($records)): ?>
                <div class="mb-12">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-black text-emerald-950 uppercase tracking-widest flex items-center gap-3">
                            <i data-lucide="trophy" class="w-6 h-6 text-amber-500"></i>
                            Top Performers Leaderboard
                        </h2>
                        <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest bg-emerald-50 px-4 py-2 rounded-full border border-emerald-100">Analytics Active</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8">
                        <?php 
                        $top_3 = array_slice($records, 0, 3);
                        foreach($top_3 as $index => $top): 
                            $medal_color = $index == 0 ? 'text-amber-400' : ($index == 1 ? 'text-slate-400' : 'text-amber-700');
                            $medal_bg = $index == 0 ? 'bg-amber-50' : ($index == 1 ? 'bg-slate-50' : 'bg-amber-50/50');
                        ?>
                        <div class="bg-white p-6 md:p-8 rounded-[1.5rem] md:rounded-[2.5rem] border-2 border-emerald-100 shadow-xl shadow-emerald-900/5 relative overflow-hidden group hover:border-emerald-500 transition-all duration-300">
                            <div class="absolute top-0 right-0 p-6">
                                <div class="w-12 h-12 rounded-2xl <?php echo $medal_bg; ?> flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i data-lucide="medal" class="w-6 h-6 <?php echo $medal_color; ?>"></i>
                                </div>
                            </div>
                            <div class="mb-6">
                                <p class="text-[10px] font-black text-emerald-400 uppercase tracking-[0.3em] mb-2">Rank #<?php echo $index + 1; ?></p>
                                <h3 class="text-xl font-black text-emerald-950 uppercase leading-none group-hover:text-emerald-600 transition-colors"><?php echo $top['firstname'].' '.$top['lastname']; ?></h3>
                                <p class="text-[9px] font-bold text-emerald-300 uppercase tracking-widest"><?php echo $top['school_id']; ?></p>
                            </div>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="text-3xl font-black text-emerald-950"><?php echo $top['overall_rating']; ?></p>
                                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest">Final Rating</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-black text-emerald-950"><?php echo $top['total_students']; ?></p>
                                    <p class="text-[9px] font-black text-emerald-300 uppercase tracking-widest">Respondents</p>
                                </div>
                            </div>
                            <div class="mt-6 pt-6 border-t border-emerald-50 flex items-center justify-between">
                                <span class="text-[10px] font-black text-emerald-950 uppercase tracking-tighter bg-emerald-50 px-3 py-1 rounded-lg">
                                    <?php echo $top['year']; ?> - <?php echo $top['semester'] == 1 ? '1st' : '2nd'; ?> Sem
                                </span>
                                <div class="flex gap-1">
                                    <?php for($i=0; $i<5; $i++): ?>
                                    <i data-lucide="star" class="w-3 h-3 <?php echo $i < round($top['overall_rating']) ? 'text-amber-400 fill-amber-400' : 'text-slate-200'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bolder Filter Panel -->
                <div class="bg-white p-10 rounded-[2.5rem] border-4 border-emerald-950 shadow-2xl shadow-emerald-900/10 mb-12 no-print relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <i data-lucide="search" class="w-24 h-24 text-emerald-950"></i>
                    </div>
                    <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-8 relative z-10">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-emerald-400 uppercase tracking-widest block ml-1">Select School Year</label>
                            <select name="year" class="w-full bg-emerald-50/50 px-6 py-4 rounded-2xl border-2 border-emerald-100 focus:border-emerald-950 focus:ring-0 outline-none text-sm font-black transition-all appearance-none cursor-pointer">
                                <option value="">All Archived Years</option>
                                <?php
                                $years = $conn->query("SELECT DISTINCT year FROM academic_list WHERE status = 2 ORDER BY year DESC");
                                while($y = $years->fetch_assoc()):
                                ?>
                                <option value="<?php echo $y['year']; ?>" <?php echo ($f_year == $y['year']) ? 'selected' : ''; ?>><?php echo $y['year']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-emerald-400 uppercase tracking-widest block ml-1">Select Semester</label>
                            <select name="semester" class="w-full bg-emerald-50/50 px-6 py-4 rounded-2xl border-2 border-emerald-100 focus:border-emerald-950 focus:ring-0 outline-none text-sm font-black transition-all appearance-none cursor-pointer">
                                <option value="0">All Semesters</option>
                                <option value="1" <?php echo ($f_semester == 1) ? 'selected' : ''; ?>>1st Semester</option>
                                <option value="2" <?php echo ($f_semester == 2) ? 'selected' : ''; ?>>2nd Semester</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-emerald-400 uppercase tracking-widest block ml-1">Faculty Selection</label>
                            <select name="faculty_id" class="w-full bg-emerald-50/50 px-6 py-4 rounded-2xl border-2 border-emerald-100 focus:border-emerald-950 focus:ring-0 outline-none text-sm font-black transition-all appearance-none cursor-pointer">
                                <option value="0">Show All Faculty</option>
                                <?php
                                $faculties = $conn->query("SELECT id, firstname, lastname FROM faculty_list WHERE is_deleted = 0 ORDER BY lastname ASC");
                                while($f = $faculties->fetch_assoc()):
                                ?>
                                <option value="<?php echo $f['id']; ?>" <?php echo ($f_faculty == $f['id']) ? 'selected' : ''; ?>><?php echo $f['lastname'].', '.$f['firstname']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-emerald-950 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-emerald-900 transition-all shadow-xl shadow-emerald-900/20 active:scale-95 flex items-center justify-center gap-2">
                                <i data-lucide="sliders" class="w-4 h-4"></i>
                                View Archive
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bolder Table UI -->
                <div class="bg-white rounded-[2.5rem] border-2 border-emerald-950 shadow-2xl shadow-emerald-900/10 overflow-hidden mb-12">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[1000px]">
                            <thead>
                                <tr class="bg-emerald-950 border-b-2 border-emerald-950">
                                    <th class="px-8 py-8 text-[11px] font-black text-emerald-300 uppercase tracking-[0.2em] text-center">Rank</th>
                                    <th class="px-10 py-8 text-[11px] font-black text-emerald-300 uppercase tracking-[0.2em]">Faculty Identity</th>
                                    <th class="px-10 py-8 text-[11px] font-black text-emerald-300 uppercase tracking-[0.2em] text-center">Period</th>
                                    <th class="px-10 py-8 text-[11px] font-black text-emerald-300 uppercase tracking-[0.2em] text-center">Respondents</th>
                                    <th class="px-10 py-8 text-[11px] font-black text-emerald-300 uppercase tracking-[0.2em] text-center">Final Rating</th>
                                    <th class="px-10 py-8 text-[11px] font-black text-emerald-300 uppercase tracking-[0.2em] text-center">Performance</th>
                                    <th class="px-10 py-8 text-[11px] font-black text-emerald-300 uppercase tracking-[0.2em] text-right">Verification</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-emerald-50">
                                <?php if(empty($records)): ?>
                                <tr>
                                    <td colspan="7" class="px-10 py-32 text-center">
                                        <div class="flex flex-col items-center opacity-20">
                                            <i data-lucide="archive-x" class="w-20 h-20 mb-4"></i>
                                            <p class="text-xl font-black uppercase tracking-widest">No Archived Records Found</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: $rank = 1; foreach($records as $row): ?>
                                <tr class="hover:bg-emerald-50/50 transition-all duration-300 group">
                                    <td class="px-8 py-8 text-center border-r border-emerald-50">
                                        <?php if($rank <= 3): ?>
                                            <div class="relative inline-block">
                                                <div class="w-10 h-10 rounded-full <?php echo $rank == 1 ? 'bg-amber-400' : ($rank == 2 ? 'bg-slate-300' : 'bg-amber-600'); ?> flex items-center justify-center text-white font-black shadow-lg">
                                                    <?php echo $rank; ?>
                                                </div>
                                                <i data-lucide="crown" class="w-4 h-4 text-amber-500 absolute -top-2 -right-2 rotate-12"></i>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xl font-black text-emerald-300"><?php echo $rank; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-10 py-8 border-r border-emerald-50">
                                        <div class="flex items-center gap-6">
                                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center font-black text-emerald-950 text-xl shadow-inner group-hover:bg-emerald-950 group-hover:text-white transition-all duration-300">
                                                <?php echo substr($row['firstname'], 0, 1).substr($row['lastname'], 0, 1); ?>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-black text-emerald-950 text-lg uppercase tracking-tight leading-none mb-1 group-hover:translate-x-1 transition-transform"><?php echo $row['firstname'].' '.$row['lastname']; ?></span>
                                                <span class="text-[10px] text-emerald-400 font-black uppercase tracking-[0.3em]"><?php echo $row['school_id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-center border-r border-emerald-50">
                                        <div class="inline-flex flex-col bg-slate-800 text-white px-6 py-2 rounded-xl mb-1">
                                            <span class="font-black text-sm whitespace-nowrap"><?php echo $row['year']; ?></span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-emerald-600 font-black uppercase tracking-widest"><?php echo ($row['semester'] == 1) ? '1ST' : '2ND'; ?> SEMESTER</span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-center border-r border-emerald-50">
                                        <p class="text-2xl font-black text-emerald-950"><?php echo $row['total_students']; ?></p>
                                        <p class="text-[9px] font-black text-emerald-300 uppercase tracking-widest">Students Rated</p>
                                    </td>
                                    <td class="px-10 py-8 text-center border-r border-emerald-50">
                                        <div class="relative inline-block">
                                            <span class="text-4xl font-black text-emerald-950 relative z-10"><?php echo $row['overall_rating']; ?></span>
                                            <div class="absolute -bottom-1 left-0 w-full h-3 bg-emerald-200 -z-10 rounded-full"></div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-center border-r border-emerald-50">
                                        <?php
                                        $bg = ""; $txt = "";
                                        switch($row['descriptive']) {
                                            case 'Outstanding': $bg = 'bg-emerald-600'; $txt = 'text-white'; break;
                                            case 'Very Satisfactory': $bg = 'bg-emerald-400'; $txt = 'text-white'; break;
                                            case 'Satisfactory': $bg = 'bg-blue-500'; $txt = 'text-white'; break;
                                            case 'Fair': $bg = 'bg-amber-500'; $txt = 'text-white'; break;
                                            case 'Poor': $bg = 'bg-red-600'; $txt = 'text-white'; break;
                                            default: $bg = 'bg-slate-200'; $txt = 'text-slate-600'; break;
                                        }
                                        ?>
                                        <span class="<?php echo $bg.' '.$txt; ?> px-6 py-2.5 rounded-full text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-950/20 whitespace-nowrap border-2 border-white">
                                            <?php echo $row['descriptive']; ?>
                                        </span>
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <div class="flex flex-col items-end">
                                            <span class="text-xs font-black text-emerald-950 uppercase tracking-widest mb-1 flex items-center gap-2">
                                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                                                ARCHIVED
                                            </span>
                                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-tighter">DATA LOCKED</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php $rank++; endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        function exportArchive() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = `export_archive.php?${params.toString()}`;
        }
    </script>
</body>
</html>
