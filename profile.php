<?php
session_start();
require_once('config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$comments_count = 0;
try {
    $comm_stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
    $comm_stmt->execute([$user_id]);
    $comments_count = $comm_stmt->fetchColumn();
} catch (Exception $e) {

}

$posts_count = 0;
try {
    $post_stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
    $post_stmt->execute([$user_id]);
    $posts_count = $post_stmt->fetchColumn();
} catch (Exception $e) {

}

$formatted_date = date('d F Y', strtotime($user['created_at'] ?? 'now'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$haNews | Profile</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="assets/js/main.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
    @theme {
        --color-clifford: #da373d;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col overflow-x-auto bg-slate-100 text-slate-950 selection:bg-blue-200">
<header class="relative z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-12">
    <div class="flex-1"></div>
    <div class="flex justify-center flex-1">
        <a href="index.php" class="flex items-center gap-2.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-700 select-none" draggable="false">
            <span class="text-[1.24em] font-extrabold tracking-[-0.03em] text-slate-950 select-none">$HANEWS</span>
        </a>
    </div>

    <div class="flex items-center justify-end flex-1 gap-4">
        <div class="relative flex items-center">
            <div id="searchBar" class="hidden absolute right-12 bg-white border border-slate-200 rounded-xl px-3 py-1.5 w-90 z-30">
                <form action="index.php" method="GET" class="flex items-center gap-2">
                    <input id="searchInput" type="text" name="search" placeholder="Search news..." class="w-full bg-transparent text-slate-800 text-sm focus:outline-none py-0.5" autocomplete="off">
                    <button id="closeSearchBtn" type="button" class="text-slate-400 hover:text-slate-950 text-xs uppercase font-medium px-1.5 py-0.5 rounded hover:bg-slate-100 transition">ESC</button>
                </form>
            </div>
            
            <button id="searchToggleBtn" type="button" class="p-2 text-slate-600 hover:text-slate-950 border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition cursor-pointer" title="Search">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>
        </div>
        
        <div class="relative">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button onclick="toggleMenu('userMenu')" class="flex items-center gap-2 focus:outline-none p-1 rounded-xl hover:bg-slate-100 transition cursor-pointer">
                    <div class="w-9 h-9 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold text-sm">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </button>

                <div id="userMenu" class="hidden fixed right-1 top-16 w-64 bg-white border border-slate-200/80 rounded-2xl shadow-xl shadow-slate-900/10 p-2 z-50">
                    <div class="flex items-center gap-3 px-3 py-3 mb-2 rounded-xl bg-slate-50">
                        <div class="w-10 h-10 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold text-sm shrink-0">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-500">Signed in as</p>
                            <p class="text-sm font-semibold text-slate-900 truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        </div>
                    </div>

                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 rounded-xl hover:bg-slate-100 transition">
                        <svg class="size-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Profile
                    </a>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin_posts.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 rounded-xl hover:bg-slate-100 transition">
                            <svg class="size-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H7.5A2.25 2.25 0 0 0 5.25 4.5v15A2.25 2.25 0 0 0 7.5 21.75h9.75a2.25 2.25 0 0 0 2.25-2.25v-1.5" />
                            </svg>
                            Posts
                        </a>
                    <?php endif; ?>

                    <div class="border-t border-slate-100 my-2"></div>
                    <a href="logout.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-rose-600 rounded-xl hover:bg-rose-50 transition font-medium">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-6 3 3m0 0-3 3m3-3H9" />
                        </svg>
                        Log Out
                    </a>
                </div>

            <?php else: ?>
                <button onclick="toggleMenu('guestMenu')" class="p-2 text-slate-600 hover:text-slate-950 border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition focus-visible:outline-2 focus-visible:outline-blue-700 cursor-pointer" title="Profile">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </button>

                <div id="guestMenu" class="hidden fixed right-1 top-16 w-64 bg-white border border-slate-200/80 rounded-2xl shadow-xl shadow-slate-900/10 p-2 z-50">
                    <div class="flex items-center gap-3 px-3 py-4 mb-2 rounded-xl bg-slate-50">
                        <div class="w-11 h-11 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Welcome back</p>
                            <p class="text-base font-semibold text-slate-900">Guest</p>
                        </div>
                    </div>

                    <a href="login.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 rounded-xl hover:bg-slate-100 transition font-medium">
                        <svg class="size-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-6 3 3m0 0-3 3m3-3H9" />
                        </svg>
                        Log in
                    </a>

                    <a href="register.php" class="flex items-center justify-center gap-2 px-3 py-2.5 mt-1 text-sm text-white bg-slate-950 hover:bg-slate-800 rounded-xl transition font-semibold">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3.75 20.118a7.5 7.5 0 0 1 14.998 0" />
                        </svg>
                        Sign Up
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>  
</header>

<div class="border-b border-slate-200/80 bg-slate-50/60 py-6">
    <div class="max-w-[1800px] mx-auto flex items-center justify-between text-sm text-slate-500 font-medium">
        <a href="index.php" class="inline-flex items-center gap-1.5 hover:text-slate-950 transition font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to stories
        </a>
    </div>
</div>


<main class="flex-1 bg-white">
    <div class="max-w-[1400px] mx-auto px-8 pt-14 pb-12">
        <div class="mb-12">
            <span class="text-sm font-bold uppercase tracking-[0.14em] text-blue-600">Account</span>
            <h1 class="text-5xl tracking-[-0.03em] font-bold text-slate-950 mt-2.5" style="font-family: 'Newsreader', serif;">User profile</h1>
            <p class="text-slate-500 text-lg mt-1.5 tracking-[-0.01em]">Manage your personal details and account security on $HANEWS.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 bg-white border border-slate-200 rounded-2xl p-8 shadow-sm space-y-8">
                <div class="flex items-center pb-6 border-b border-slate-200">
                    <div class="flex items-center gap-5">
                        <div class="w-18 h-18 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold text-2xl shadow-md select-none">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-slate-900" style="font-family: 'Newsreader', serif;"><?php echo htmlspecialchars($user['username']); ?></h2>
                            <p class="text-base text-slate-500 font-medium mt-0.5">
                                <?php echo ($user['role'] === 'admin') ? 'Administrator SHANEWS' : 'Reader SHANEWS'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Personal information</h3>
                    
                    <div class="flex justify-between items-center py-4 border-b border-slate-100 text-base">
                        <span class="text-slate-500">Username</span>
                        <span class="font-semibold text-slate-900 text-lg"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>

                    <div class="flex justify-between items-center py-4 border-b border-slate-100 text-base">
                        <span class="text-slate-500">Email address</span>
                        <span class="font-semibold text-slate-900 text-lg"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>

                    <div class="flex justify-between items-center py-4 text-base">
                        <span class="text-slate-500">Member since</span>
                        <span class="font-semibold text-slate-900 text-lg"><?php echo $formatted_date; ?></span>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-2xl p-7 shadow-sm space-y-5">
                    <span class="text-sm font-bold uppercase tracking-[0.14em] text-blue-600">Activity</span>
                    <h3 class="text-3xl font-bold text-slate-900" style="font-family: 'Newsreader', serif;">Your stats</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                            <span class="text-4xl font-bold text-slate-900" style="font-family: 'Newsreader', serif;"><?php echo $comments_count; ?></span>
                            <p class="text-sm text-slate-500 mt-1 font-medium">comments</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                            <span class="text-4xl font-bold text-slate-900" style="font-family: 'Newsreader', serif;"><?php echo $posts_count; ?></span>
                            <p class="text-sm text-slate-500 mt-1 font-medium">posts</p>
                        </div>
                    </div>

                    <a href="my_comments.php" class="w-full py-3 px-4 border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-800 text-base font-semibold rounded-xl transition cursor-pointer flex items-center justify-center gap-2">
                        <svg class="size-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.189.93 4.178 2.457 5.578.334.325.557.77.467 1.226-.19 1.002-.756 2.115-1.096 2.384a.75.75 0 0 0 .827 1.218c1.32-.569 2.707-1.571 3.277-2.008C10.052 19.982 10.99 20.25 12 20.25Z" />
                        </svg>
                        My comments
                    </a>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-3.5">
                        <div class="p-2.5 bg-blue-50 text-blue-700 rounded-xl">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-slate-900">Security</h4>
                            <p class="text-sm text-slate-500">Password and access</p>
                        </div>
                    </div>
                    
                    <p class="text-slate-500 text-base leading-relaxed">Update your password regularly to help keep your account secure.</p>

                    <a href="profile_password.php" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold rounded-xl transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                        <svg class="size-5 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.149 17.5467L6.778 18.9217L5 17.1387L11.388 10.7387C10.5317 8.96445 11.0948 6.83093 12.715 5.71029C14.3353 4.58965 16.5303 4.8156 17.8882 6.24281C19.2462 7.67002 19.3628 9.8735 18.1631 11.4361C16.9633 12.9987 14.8045 13.455 13.075 12.5117L9.563 16.0337L11.03 17.5057L9.569 18.9707L8.149 17.5467Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.128 7.85071C16.7515 8.47602 16.7506 9.48824 16.1258 10.1124C15.5011 10.7365 14.4889 10.7365 13.8642 10.1124C13.2395 9.48824 13.2385 8.47602 13.862 7.85071C14.4893 7.22864 15.5008 7.22864 16.128 7.85071Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        Change password
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>


<footer class="bg-white border-t border-slate-200 text-slate-500 text-sm py-8 px-6 md:px-16">
  <div class="max-w-[1800px] w-full mx-auto px-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
    <div class="flex flex-col gap-6">
      <div class="text-[1.43em] font-extrabold tracking-[-0.03em] text-slate-950 select-none">
        $HANEWS
      </div>
      <div class="text-sm text-slate-400">
        © 2026 $HANEWS. All rights reserved.
      </div>
    </div>

    <div class="flex flex-col items-start md:items-end gap-8">
      <nav class="flex flex-wrap gap-x-8 gap-y-2 text-sm" style="font-family: 'Inter', sans-serif;">
        <a href="#" class="hover:text-slate-800 transition-colors">About us</a>
        <a href="#" class="hover:text-slate-800 transition-colors">Our journalists</a>
        <a href="#" class="hover:text-slate-800 transition-colors">Contact</a>
        <a href="#" class="hover:text-slate-800 transition-colors">Help</a>
      </nav>

      <nav class="flex flex-wrap gap-x-8 gap-y-2 text-slate-400 text-sm">
        <a href="#" class="hover:text-slate-600 transition-colors">Privacy policy</a>
        <a href="#" class="hover:text-slate-600 transition-colors">Terms of use</a>
      </nav>
    </div>
  </div>
</footer>

</body>
</html>