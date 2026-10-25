<?php
session_start();
require_once('config/db.php');

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if(!empty($username) && !empty($password)){
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            if(isset($_POST['remember'])){
                $token = bin2hex(random_bytes(32));

                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);

                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            }

            header('Location: index.php');
            exit;
        }else{
            $message = 'Invalid username/email or password!';
        }
    }else{
        $message = 'Fill in all the fields!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$haNews | Log In</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="assets/js/main.js" defer></script>
    <style type="text/tailwindcss">
    @theme {
        --color-clifford: #da373d;
    }
    </style>
</head>
<body class="min-h-screen flex flex-col overflow-x-hidden bg-slate-100 text-slate-950 selection:bg-blue-200">
<header class="relative z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-5 sm:px-8 lg:px-12">
    <div class="flex-1"></div>
    <div class="flex justify-center flex-1">
        <a href="index.php" class="flex items-center gap-2.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-700 select-none" draggable="false">
            <span class="text-[1.24em] font-extrabold tracking-[-0.03em] text-slate-950 select-none">$HANEWS</span>
        </a>
    </div>

    <div class="flex items-center justify-end flex-1 gap-4">
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
                        <div class="w-11 h-11 rounded-full bg-slate-200 text-white-700 flex items-center justify-center shrink-0">
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



<section class="flex-grow relative flex items-center justify-center px-5 py-12">

    <img src="assets/images/newspaper-bg.png" alt="Newspaper background" class="absolute inset-0 m-auto max-w-[980px] w-full pointer-events-none select-none z-0 object-contain" draggable="false">

    <div class="relative z-10 w-full max-w-[406px] border border-slate-300 bg-white p-6 shadow-[0_12px_35px_rgba(15,23,42,0.06)] sm:p-9">
        <div class="mb-10 text-center">
            <span class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Welcome Back</span>
            <h1 class="text-3xl font-extrabold tracking-[-0.04em]">Log in to account</h1>
            <p class="mt-2 text-sm text-slate-500">Enter your credentials to access your profile.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-3 rounded-xl border p-4 text-sm font-medium transition-all duration-200 border-rose-200 bg-rose-50 text-rose-800">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 shrink-0 text-rose-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-5">
            <div class="mb-7">
                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Username</label>
                <div class="mt-2 relative">
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" placeholder="Your username" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 transition-all focus:border-slate-950 focus:outline-none focus:ring-1 focus:ring-slate-950">
                </div>
            </div>
            
            <div class="mb-8">
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Password</label>
                <div class="mt-2 relative">
                    <input type="password" id="password" name="password" placeholder="Your password" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 transition-all focus:border-slate-950 focus:outline-none focus:ring-1 focus:ring-slate-950 pr-10">
                    <button type="button" id="togglePasswordBtn" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition" title="Show / hide password">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm mb-5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-slate-950 focus:ring-slate-950 size-4">
                    <span class="text-slate-600 font-medium">Remember Me</span>
                </label>
                <a href="forgot.php" class="font-medium text-slate-600 hover:text-slate-950 transition">Forgot password?</a>
            </div>

            <button type="submit" class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-slate-800 active:scale-[0.99] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950 cursor-pointer">
                <span>Log in</span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </button>
        </form>

        <p class="mt-8 text-center text-sm font-medium text-slate-500">Don't have an account? <a href="register.php" class="font-bold text-slate-950 hover:underline">Sign Up</a></p>
    </div>
</section>


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
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');

    togglePasswordBtn.addEventListener('click', ()=>{
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        
        togglePasswordBtn.classList.toggle('text-slate-950', isPassword);
        togglePasswordBtn.classList.toggle('text-slate-400', !isPassword);
    });
</script>
</body>
</html>