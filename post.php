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

$likes_stmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ? AND type = 'like'");
$likes_stmt->execute([$post_id]);
$likes_count = $likes_stmt->fetchColumn();

$dislikes_stmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ? AND type = 'dislike'");
$dislikes_stmt->execute([$post_id]);
$dislikes_count = $dislikes_stmt->fetchColumn();

$reactions_total = $likes_count + $dislikes_count;

$user_reaction = null;
if (isset($_SESSION['user_id'])) {
    $ur_stmt = $pdo->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
    $ur_stmt->execute([$post_id, $_SESSION['user_id']]);
    $user_reaction = $ur_stmt->fetchColumn();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $content = trim($_POST['content']);

    if (!empty($content)) {
        $insert_stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        if ($insert_stmt->execute([$post_id, $_SESSION['user_id'], $content])) {
            header("Location: post.php?id=" . $post_id);
            exit;
        } else {
            $message = 'Error posting comment.';
        }
    }
}

$comments_stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at DESC");
$comments_stmt->execute([$post_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post['title']?> | $haNews</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="assets/js/main.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
    @theme {
        --color-clifford: #da373d;
        }

        article p{
            font-size: 1.25rem;
            line-height: 1.8;
            color: #334155;
            margin-bottom: 1.5rem;
        }
        article h1, article h2, article h3{
            font-family: 'Newsreader', serif;
            font-weight: 700;
            color: #020617;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
        }
        article h3{
            font-size: 1.875rem;
            line-height: 1.3;
        }
        article blockquote{
            border-left: 3px solid #3b82f6;
            padding-left: 1.5rem;
            margin: 3rem 0;
            font-family: 'Newsreader', serif;
            font-size: 1.875rem;
            font-weight: 700;
            line-height: 1.4;
            color: #020617;
            font-style: normal;
        }
        article ul, article ol{
            margin-left: 1.5rem;
            margin-bottom: 1.5rem;
            color: #334155;
        }
        article ul{ list-style-type: disc; }
        article ol{ list-style-type: decimal; }
    </style>
</head>
<body class="min-h-screen flex flex-col overflow-x-auto bg-slate-100 text-slate-950 selection:bg-blue-200">
<header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-12">
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

    <div class="max-w-[1050px] mx-auto px-6 mb-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-y border-slate-200/80 py-6">
            <div class="flex items-center gap-3">
                <button type="button" 
                        id="like-btn"
                        data-post-id="<?php echo $post_id; ?>"
                        data-action="like"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold transition cursor-pointer 
                        <?php echo ($user_reaction === 'like') ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'; ?>">
                    <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="size-5">
                        <path d="M2,22H18.644a3.036,3.036,0,0,0,3-2.459l1.305-7a2.962,2.962,0,0,0-.637-2.439A3.064,3.064,0,0,0,19.949,9H15.178V5c0-2.061-2.113-3-4.076-3a1,1,0,0,0-1,1c0,1.907-.34,3.91-.724,4.284L6.593,10H2a1,1,0,0,0-1,1V21A1,1,0,0,0,2,22ZM8,11.421l2.774-2.7c.93-.907,1.212-3.112,1.3-4.584.542.129,1.109.38,1.109.868v5a1,1,0,0,0,1,1h5.771a1.067,1.067,0,0,1,.824.38.958.958,0,0,1,.21.8l-1.3,7A1.036,1.036,0,0,1,18.644,20H8ZM3,12H6v8H3Z"/>
                    </svg>
                    <span id="likes-count"><?php echo $likes_count; ?></span>
                </button>

                <button type="button" 
                        id="dislike-btn"
                        data-post-id="<?php echo $post_id; ?>"
                        data-action="dislike"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold transition cursor-pointer 
                        <?php echo ($user_reaction === 'dislike') ? 'bg-slate-900 border-slate-900 text-white shadow-sm' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'; ?>">
                    <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="size-5">
                        <path d="M20 3H6.693A2.01 2.01 0 0 0 4.82 4.298l-2.757 7.351A1 1 0 0 0 2 12v2c0 1.103.897 2 2 2h5.612L8.49 19.367a2.004 2.004 0 0 0 .274 1.802c.376.52.982.831 1.624.831H12c.297 0 .578-.132.769-.36l4.7-5.64H20c1.103 0 2-.897 2-2V5c0-1.103-.897-2-2-2zm-8.469 17h-1.145l1.562-4.684A1 1 0 0 0 11 14H4v-1.819L6.693 5H16v9.638L11.531 20zM18 14V5h2l.001 9H18z"/>
                    </svg>
                    <span id="dislikes-count"><?php echo $dislikes_count; ?></span>
                </button>
            </div>

            <div class="text-sm text-slate-400 font-medium" id="total-reactions-text">
                <?php echo $reactions_total; ?> readers reacted to this story
            </div>
        </div>
    </div>

    <?php if (!empty($images_essay)): ?>
        <section class="max-w-[1170px] mx-auto px-6 mt-26 mb-30">
            <div class="mb-6">
                <span class="text-sm font-bold uppercase tracking-widest text-blue-600 py-0.5">Photo Essay</span>
                <h2 class="text-2xl font-bold font-serif-editorial text-slate-950">Along the night line</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach ($images_essay as $img): ?>
                    <div class="flex flex-col">
                        <div class="h-80 rounded-xl overflow-hidden bg-slate-100 shadow-sm cursor-pointer group" onclick="openModal('<?php echo htmlspecialchars($img['image_path']); ?>')">
                            <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Essay photo" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="imageModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 cursor-pointer" onclick="closeModal()">
                <div class="relative max-w-5xl max-h-[90vh] overflow-hidden rounded-2xl" onclick="event.stopPropagation()">
                    <button onclick="closeModal()" class="absolute top-4 right-4 bg-slate-900/60 hover:bg-slate-900 text-white rounded-full p-2 transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <img id="modalImage" src="" alt="Full size preview" class="w-full max-h-[85vh] object-contain rounded-xl shadow-2xl">
                </div>
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

<section class="bg-white">
    <div class="max-w-[1150px] mx-auto px-6 mt-20 mb-28 border-t border-slate-200 pt-16">
        <div class="flex items-center justify-between mb-12">
            <div>
                <span class="text-sm font-bold uppercase tracking-widest text-blue-600 py-0.5">The conversation</span>
                <h2 class="text-3xl font-bold font-serif-editorial text-slate-950 mt-1">Comments (<?php echo count($comments); ?>)</h2>
            </div>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="px-5.5 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold rounded-xl transition shadow-sm tracking-wide">Join the discussion</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm mb-12">
                <h3 class="text-[1.35em] font-bold text-slate-900 mb-4" style="font-family: 'Newsreader', serif;">Share your perspective</h3>
                
                <form action="post.php?id=<?php echo $post_id; ?>" method="POST" class="space-y-4">
                    <div>
                        <textarea name="content" rows="4" required placeholder="What do you think about this story?" class="w-full rounded-xl border border-slate-200 bg-white p-4 text-base font-medium text-slate-900 placeholder-slate-400 focus:border-slate-950 focus:outline-none focus:ring-1 focus:ring-slate-950 resize-y"></textarea>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm text-slate-400 font-medium">Please keep the conversation thoughtful.</span>
                        <button type="submit" class="px-6 py-3 bg-slate-950 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl transition shadow-md cursor-pointer">
                            Post comment
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center text-base text-slate-600 mb-12">
                <p>Please <a href="login.php" class="font-semibold text-blue-600 hover:underline">Log In</a> to share your perspective.</p>
            </div>
        <?php endif; ?>

        <div class="max-w-[1050px] mx-auto space-y-8 divide-y divide-slate-100">
            <?php if (empty($comments)): ?>
                <p class="text-slate-400 text-base italic text-center py-8">No comments yet. Be the first to join the conversation!</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="pb-9 first:pt-4 not-last:border-b not-last:border-slate-200 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold text-lg shrink-0 select-none">
                            <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-1">
                                <span class="font-bold text-slate-900 text-base"><?php echo htmlspecialchars($comment['username']); ?></span>
                                <span class="text-sm text-slate-400 font-medium">
                                    <?php 
                                        $time_ago = strtotime($comment['created_at']);
                                        $diff = time() - $time_ago;
                                        if ($diff < 60) {
                                            echo 'Just now';
                                        } elseif ($diff < 3600) {
                                            echo floor($diff / 60) . ' min ago';
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . ' hr ago';
                                        } else {
                                            echo date('M d, Y', $time_ago);
                                        }
                                    ?>
                                </span>
                            </div>
                            <p class="text-slate-700 text-base leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<button id="scrollToTopBtn" class="fixed bottom-8 right-8 z-50 flex items-center justify-center w-12 h-12 rounded-xl bg-slate-950 text-white shadow-xl transition-all duration-300 opacity-0 pointer-events-none hover:bg-slate-800 cursor-pointer" title="Back to top">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
    </svg>
</button>

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

<script>
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 2000) {
            scrollToTopBtn.classList.remove('opacity-0', 'pointer-events-none');
            scrollToTopBtn.classList.add('opacity-100', 'pointer-events-auto');
        } else {
            scrollToTopBtn.classList.add('opacity-0', 'pointer-events-none');
            scrollToTopBtn.classList.remove('opacity-100', 'pointer-events-auto');
        }
    });

    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    function openModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modalImg.src = imageSrc;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const likeBtn = document.getElementById('like-btn');
        const dislikeBtn = document.getElementById('dislike-btn');

        [likeBtn, dislikeBtn].forEach(btn => {
            if (!btn) return;
            btn.addEventListener('click', async () => {
                const postId = btn.getAttribute('data-post-id');
                const action = btn.getAttribute('data-action');

                try {
                    const response = await fetch('like_handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ post_id: postId, action: action })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        if (data.error === 'Unauthorized') {
                            window.location.href = 'login.php';
                        }
                        return;
                    }

                    document.getElementById('likes-count').textContent = data.likes_count;
                    document.getElementById('dislikes-count').textContent = data.dislikes_count;
                    document.getElementById('total-reactions-text').textContent = `${data.total_reactions} readers reacted to this story`;

                    if (data.user_reaction === 'like') {
                        likeBtn.className = "inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold transition cursor-pointer bg-blue-600 border-blue-600 text-white shadow-sm";
                    } else {
                        likeBtn.className = "inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold transition cursor-pointer bg-white border-slate-200 text-slate-700 hover:bg-slate-50";
                    }

                    if (data.user_reaction === 'dislike') {
                        dislikeBtn.className = "inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold transition cursor-pointer bg-slate-900 border-slate-900 text-white shadow-sm";
                    } else {
                        dislikeBtn.className = "inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold transition cursor-pointer bg-white border-slate-200 text-slate-700 hover:bg-slate-50";
                    }

                } catch (err) {
                    console.error('Error:', err);
                }
            });
        });
    });
</script>
</body>
</html>