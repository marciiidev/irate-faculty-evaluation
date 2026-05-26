<!-- faculty/sidebar.php -->
<style>
    body {
        background-image: url('../assets/bg.Bpc.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
    }
    /* Add a subtle overlay to the body to ensure readability of the content */
    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(240, 253, 244, 0.85); /* Very light green (bg-green-50) with high opacity */
        z-index: -1;
    }
</style>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-green-950 text-white flex flex-col p-6 border-r border-white/5 transition-all transform -translate-x-full lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen shadow-2xl lg:shadow-none no-print shrink-0">
    <div class="flex items-center justify-between mb-10 lg:justify-center">
        <div class="w-24 h-24 flex items-center justify-center overflow-hidden">
            <img src="../assets/Bpc logo.png" alt="BPC Logo" class="w-full h-full object-contain">
        </div>
        <button onclick="toggleSidebar()" class="lg:hidden text-white/50 hover:text-white transition-all">
            <i data-lucide="x" class="w-8 h-8"></i>
        </button>
    </div>

    <nav class="flex-1 space-y-2 overflow-y-auto">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $nav_items = [
            ['index.php', 'layout-dashboard', 'Dashboard'],
            ['result.php', 'clipboard-check', 'Evaluation Result'],
        ];

        foreach ($nav_items as $item):
            $active = ($current_page == $item[0]) ? 'bg-white/10 text-white' : 'text-green-400 hover:bg-white/5 hover:text-white';
        ?>
        <a href="<?php echo $item[0]; ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $active; ?> font-medium transition-all">
            <i data-lucide="<?php echo $item[1]; ?>" class="w-5 h-5"></i>
            <?php echo $item[2]; ?>
        </a>
        <?php endforeach; ?>
    </nav>
</aside>
