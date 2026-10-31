<?php
session_start();
require_once('config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];

    $stmt_del = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt_del->execute([$delete_id]);
    
    header('Location: admin_posts.php');
    exit;
}

$stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | $haNews</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="assets/js/main.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-100 text-slate-950 font-sans">
<header class="relative z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-12">
    <div class="flex-1"></div>
    <div class="flex justify-center flex-1">
        <a href="index.php" class="flex items-center gap-2.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-700 select-none" draggable="false">
            <span class="text-[1.24em] font-extrabold tracking-[-0.03em] text-slate-950 select-none">$HANEWS</span>
        </a>
    </div>

    <div class="flex items-center justify-end flex-1 gap-4">
        <div class="relative">
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
                
                <a href="admin_posts.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 rounded-xl hover:bg-slate-100 transition">
                    <svg class="size-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H7.5A2.25 2.25 0 0 0 5.25 4.5v15A2.25 2.25 0 0 0 7.5 21.75h9.75a2.25 2.25 0 0 0 2.25-2.25v-1.5" />
                    </svg>
                    Posts
                </a>

                <div class="border-t border-slate-100 my-2"></div>
                <a href="logout.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-rose-600 rounded-xl hover:bg-rose-50 transition font-medium">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-6 3 3m0 0-3 3m3-3H9" />
                    </svg>
                    Log Out
                </a>
            </div>
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



<main class="max-w-[1200px] mx-auto px-6 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-950 font-serif-editorial" style="font-family: 'Newsreader', serif;">Manage Posts</h1>
            <p class="text-sm text-slate-500 mt-1">Total articles: <?php echo count($posts); ?></p>
        </div>
        <a href="admin_add_post.php" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition inline-flex items-center justify-center cursor-pointer shadow-sm gap-2 font-semibold" title="Create New Post">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="w-5 h-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>New story</span>
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-500 uppercase font-semibold text-xs tracking-wider">
                        <th class="py-4 px-6">Title</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6">Author</th>
                        <th class="py-4 px-6">Date</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 italic">No posts found. Create your first article!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $item): ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 px-6 font-bold text-slate-900 max-w-xs truncate">
                                    <a href="post.php?id=<?php echo $item['id']; ?>" class="hover:text-blue-600 transition">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </a>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-block px-2.5 py-1 text-xs font-bold uppercase rounded-md bg-slate-100 text-slate-700">
                                        <?php echo htmlspecialchars($item['category']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    <?php echo htmlspecialchars($item['username']); ?>
                                </td>
                                <td class="py-4 px-6 text-slate-400 text-xs">
                                    <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="admin_edit.php?id=<?php echo $item['id']; ?>" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                            Edit
                                        </a>
                                        <a href="admin_posts.php?action=delete&id=<?php echo $item['id']; ?>" 
                                            onclick="return confirm('Are you sure you want to delete this post?');" 
                                            class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>