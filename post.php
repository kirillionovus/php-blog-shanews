<?php 
session_start();
require_once('config/db.php');

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$stmt->execute([$post_id]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$post){
    header("Location: index.php");
    exit;
}

$images_stmt = $pdo->prepare("SELECT * FROM post_images WHERE post_id = ?");
$images_stmt->execute([$post_id]);
$images_essay = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$haNews</title>
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

<nav class="border-b border-slate-200 bg-white px-12">
    <div class="max-w-[1800px] mx-auto flex items-center justify-center">
        <div class="flex items-center gap-10 text-sm font-medium whitespace-nowrap">
            <a href="index.php" class="relative py-4 text-slate-500 hover:text-slate-950 transition">Home</a>

            <a href="index.php?category=world" class="relative py-4 text-slate-500 hover:text-slate-950 transition">World</a>

            <a href="index.php?category=business" class="relative py-4 text-slate-500 hover:text-slate-950 transition">Business</a>

            <a href="index.php?category=technology" class="relative py-4 text-slate-500 hover:text-slate-950 transition">Technology</a>

            <a href="index.php?category=science" class="relative py-4 text-slate-500 hover:text-slate-950 transition">Science</a>

            <a href="index.php?category=culture" class="relative py-4 text-slate-500 hover:text-slate-950 transition">Culture</a>

            <a href="index.php?category=sport" class="relative py-4 text-slate-500 hover:text-slate-950 transition">Sport</a>
        </div>
    </div>
</nav>

<div class="border-b border-slate-200/80 bg-slate-50/60 py-6">
    <div class="max-w-[1800px] mx-auto flex items-center justify-between text-sm text-slate-500 font-medium">
        <a href="index.php" class="inline-flex items-center gap-1.5 hover:text-slate-950 transition font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            All stories
        </a>
        <div>
            <?php echo htmlspecialchars(ucfirst($post['category'])); ?> &bull; <?php echo date('F d, Y', strtotime($post['created_at'])); ?>
        </div>
    </div>
</div>



<main class="flex-1 bg-white">
    <div class="max-w-[1100px] mx-auto px-6 pt-16 pb-8">
        <div class="flex items-center gap-2 mb-6">
            <span class="text-sm font-bold uppercase tracking-wider text-blue-600 py-0.5"><?php echo htmlspecialchars($post['category']); ?></span>
        </div>

        <h1 class="text-6xl font-semibold text-slate-950 font-serif-editorial tracking-tight leading-[1.15] mb-6" style="font-family: 'Newsreader', serif;">
            <?php echo htmlspecialchars(ucfirst($post['title'])); ?>
        </h1>

        <p class="text-lg text-slate-500 font-normal leading-relaxed mb-10">
            <?php echo htmlspecialchars($post['brief']); ?>
        </p>

        <div class="border-y border-slate-200 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold text-xs">
                        <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                    </div>
                    <span class="font-bold text-slate-900"><?php echo htmlspecialchars($post['username']); ?></span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-slate-500"><?php echo date('F d, Y, H:i', strtotime($post['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>

    <figure class="max-w-[1170px] mx-auto px-6 mb-16">
        <div class="rounded-2xl overflow-hidden bg-slate-100 shadow-sm">
            <img src="<?php echo htmlspecialchars($post['main_image']); ?>" alt="Main image" class="w-full h-[600px] object-cover">
        </div>
    </figure>

    <article class="max-w-[1050px] mx-auto px-6 space-y-8 text-slate-600 leading-relaxed">
        <div class="space-y-6">
            <?php echo $post['content']; ?>
        </div>
    </article>


    <?php if (!empty($images_essay)): ?>
        <section class="max-w-[1170px] mx-auto px-6 mt-26 mb-30">
            <div class="mb-6">
                <span class="text-sm font-bold uppercase tracking-widest text-blue-600 py-0.5">Photo Essay</span>
                <h2 class="text-2xl font-bold font-serif-editorial text-slate-950">Along the night line</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach ($essay_images as $img): ?>
                    <div class="flex flex-col">
                        <div class="h-80 rounded-xl overflow-hidden bg-slate-100 shadow-sm">
                            <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Essay photo" class="w-full h-full object-cover">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mt-14 border-t border-slate-200/80 py-5">
                <div>
                    <span class="block text-base text-slate-400 font-medium mb-4">Filed under:</span>
                    <div class="flex items-center gap-2">
                        <a href="index.php?category=<?php echo htmlspecialchars($post['category']); ?>" class="text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3 py-1 rounded-md transition">
                            <?php echo htmlspecialchars(ucfirst($post['category'])); ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    <?php else: echo "<div class='my-30'></div>";?>
    <?php endif; ?>
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