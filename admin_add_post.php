<?php
session_start();
require_once('config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $brief = trim($_POST['brief']);
    $category = trim($_POST['category']);
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $essay_file_count = 0;
    if (isset($_FILES['essay_images']) && !empty($_FILES['essay_images']['name'][0])) {
        $essay_file_count = count($_FILES['essay_images']['name']);
    }

    if (empty($title) || empty($content) || empty($category)) {
        $error = "Please fill in all required fields.";
    } elseif ($essay_file_count > 4) {
        $error = "You can upload a maximum of 4 essay images.";
    } else {
        $main_image_path = '';
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($_FILES['main_image']['name']);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $target_file)) {
                $main_image_path = $target_file;
            }
        }

        if (empty($main_image_path)) {
            $main_image_path = 'uploads/default_cover.jpg';
        }

        $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, brief, category, main_image, content, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $title, $brief, $category, $main_image_path, $content]);
        $post_id = $pdo->lastInsertId();

        if ($essay_file_count > 0) {
            for ($i = 0; $i < $essay_file_count; $i++) {
                if ($_FILES['essay_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $essay_name = time() . '_' . $i . '_' . basename($_FILES['essay_images']['name'][$i]);
                    $essay_target = 'uploads/' . $essay_name;
                    if (move_uploaded_file($_FILES['essay_images']['tmp_name'][$i], $essay_target)) {
                        $img_stmt = $pdo->prepare("INSERT INTO post_images (post_id, image_path) VALUES (?, ?)");
                        $img_stmt->execute([$post_id, $essay_target]);
                    }
                }
            }
        }

        $success = "Post successfully created! <a href='post.php?id={$post_id}' class='underline font-semibold'>View post</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Post &mdash; $haNews Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="assets/js/main.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
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



<main class="flex-1 max-w-3xl w-full mx-auto px-6 py-12">
    <div class="mb-8">
        <h1 class="text-3xl font-bold font-serif-editorial" style="font-family: 'Newsreader', serif;">Create New Story</h1>
        <p class="text-slate-500 text-sm mt-1">Fill out the fields below to publish a new article to $haNews.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><?php echo $success; ?></div>
    <?php endif; ?>

    <form id="postForm" action="admin_add_post.php" method="POST" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm space-y-6">
        
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Title</label>
            <input type="text" name="title" required placeholder="e.g. Europe’s night trains are making a comeback" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 text-sm focus:outline-none focus:border-slate-400 transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Brief / Subtitle</label>
            <textarea name="brief" rows="3" placeholder="Short summary under the title..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 text-sm focus:outline-none focus:border-slate-400 transition"></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
            <select name="category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 text-sm focus:outline-none focus:border-slate-400 transition">
                <option value="world">World</option>
                <option value="business">Business</option>
                <option value="technology">Technology</option>
                <option value="science">Science</option>
                <option value="culture">Culture</option>
                <option value="sport">Sport</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Main Cover Image</label>
            <input type="file" name="main_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 file:cursor-pointer">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Content</label>
            <div id="editor" style="height: 300px;" class="bg-white rounded-xl"></div>
            <input type="hidden" name="content" id="content">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Photo Essay Images (Multiple)</label>
            <input type="file" name="essay_images[]" accept="image/*" multiple class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 file:cursor-pointer">
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-slate-950 hover:bg-slate-800 text-white font-semibold text-sm rounded-xl transition cursor-pointer">
                Publish Article
            </button>
        </div>
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Write your story here...',
            modules: {
                toolbar: [
                    [{ header: [3, false] }],

                    ['bold', 'italic', 'underline', 'strike'],

                    ['blockquote'],

                    [{ list: 'ordered' }, { list: 'bullet' }],

                    ['clean']
                ]
            }
        });

        document.getElementById('postForm').addEventListener('submit', function() {
            const htmlContent = quill.root.innerHTML;
            document.getElementById('content').value = htmlContent;
        });

        document.getElementById('essayImagesInput').addEventListener('change', function() {
        if (this.files.length > 4) {
            alert('You can only upload a maximum of 4 images!');
            this.value = '';
        }
    });
</script>
</body>
</html>